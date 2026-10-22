<?php

namespace App\Services;

use App\Exceptions\VersionConflict;
use App\Models\Attachment;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProgressDraftService
{
    /**
     * Simpan atau perbarui draft parsial secara transaksional dan aman terhadap konkurensi.
     *
     * @throws VersionConflict|ValidationException
     */
    public function saveDraft(
        User $admin,
        array $payload,
        ?string $draftId = null,
        ?int $progressId = null,
        ?int $baseRevision = null,
        ?int $expectedDraftRevision = null,
        int $requestSequence = 1
    ): ProgressDraft {
        return DB::transaction(function () use (
            $admin,
            $payload,
            $draftId,
            $progressId,
            $baseRevision,
            $expectedDraftRevision,
            $requestSequence
        ) {
            // Pastikan schema_version ada di payload
            $payload['schema_version'] ??= 1;

            if ($progressId !== null) {
                // Draft Edit untuk progress yang sudah ada
                $progress = Progress::lockForUpdate()->find($progressId);
                if (! $progress) {
                    throw ValidationException::withMessages([
                        'progress_id' => 'Progress target tidak ditemukan.',
                    ]);
                }

                if ($progress->status === 'Closed') {
                    throw ValidationException::withMessages([
                        'status' => 'Progress berstatus Closed tidak dapat diedit atau diautosave.',
                    ]);
                }

                $currentBaseRevision = $baseRevision ?? $progress->revision;

                // Cari draft edit aktif untuk admin dan progress ini
                $draft = ProgressDraft::where('admin_id', $admin->id)
                    ->where('progress_id', $progressId)
                    ->where('state', 'active')
                    ->lockForUpdate()
                    ->first();

                if (! $draft) {
                    // Buat draft edit baru
                    $draft = ProgressDraft::create([
                        'id' => $draftId ?: (string) Str::uuid(),
                        'admin_id' => $admin->id,
                        'progress_id' => $progressId,
                        'base_revision' => $currentBaseRevision,
                        'draft_revision' => 1,
                        'request_sequence' => $requestSequence,
                        'schema_version' => (int) $payload['schema_version'],
                        'payload' => $payload,
                        'state' => 'active',
                    ]);

                    return $draft;
                }

                // Cek konflik jika base_revision pada draft berbeda dari progress aktual
                if ($draft->base_revision !== null && (int) $draft->base_revision < (int) $progress->revision) {
                    throw new VersionConflict();
                }

                // Cek optimistik CAS draft_revision jika diharapkan
                if ($expectedDraftRevision !== null && (int) $draft->draft_revision !== (int) $expectedDraftRevision) {
                    throw new VersionConflict();
                }

                // Lindungi urutan request: jika requestSequence lebih lama dari yang sudah tersimpan, abaikan pembaruan isi (AT17)
                if ($requestSequence < (int) $draft->request_sequence) {
                    return $draft;
                }

                $draft->draft_revision += 1;
                $draft->request_sequence = max((int) $draft->request_sequence, $requestSequence);
                $draft->payload = $payload;
                $draft->save();

                return $draft;
            }

            // Draft Baru (progress_id is null)
            $draft = null;
            if ($draftId) {
                $draft = ProgressDraft::where('id', $draftId)
                    ->where('admin_id', $admin->id)
                    ->where('state', 'active')
                    ->lockForUpdate()
                    ->first();
            }

            if (! $draft) {
                $draft = ProgressDraft::create([
                    'id' => $draftId ?: (string) Str::uuid(),
                    'admin_id' => $admin->id,
                    'progress_id' => null,
                    'base_revision' => null,
                    'draft_revision' => 1,
                    'request_sequence' => $requestSequence,
                    'schema_version' => (int) $payload['schema_version'],
                    'payload' => $payload,
                    'state' => 'active',
                ]);

                return $draft;
            }

            // Cek optimistik CAS draft_revision jika diharapkan
            if ($expectedDraftRevision !== null && (int) $draft->draft_revision !== (int) $expectedDraftRevision) {
                throw new VersionConflict();
            }

            // Lindungi urutan request
            if ($requestSequence < (int) $draft->request_sequence) {
                return $draft;
            }

            $draft->draft_revision += 1;
            $draft->request_sequence = max((int) $draft->request_sequence, $requestSequence);
            $draft->payload = $payload;
            $draft->save();

            return $draft;
        });
    }

    /**
     * Ambil draft aktif berdasarkan ID.
     */
    public function getDraft(User $admin, string $draftId): ?ProgressDraft
    {
        return ProgressDraft::where('id', $draftId)
            ->where('admin_id', $admin->id)
            ->where('state', 'active')
            ->first();
    }

    /**
     * Ambil draft edit aktif untuk progress tertentu.
     */
    public function getActiveEditDraft(User $admin, int $progressId): ?ProgressDraft
    {
        return ProgressDraft::where('admin_id', $admin->id)
            ->where('progress_id', $progressId)
            ->where('state', 'active')
            ->first();
    }

    /**
     * Daftar seluruh draft aktif milik admin.
     */
    public function listActiveDrafts(User $admin): Collection
    {
        return ProgressDraft::with(['progress.project', 'attachments'])
            ->where('admin_id', $admin->id)
            ->where('state', 'active')
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * Buang draft aktif dan hapus berkas staging miliknya.
     */
    public function discardDraft(User $admin, ProgressDraft|string $draft): bool
    {
        $draftModel = is_string($draft) ? $this->getDraft($admin, $draft) : $draft;

        if (! $draftModel || (int) $draftModel->admin_id !== (int) $admin->id || $draftModel->state !== 'active') {
            return false;
        }

        return DB::transaction(function () use ($admin, $draftModel) {
            $attachmentService = app(AttachmentService::class);

            // Hapus berkas staging yang terhubung ke draft ini
            $stagingAttachments = Attachment::where('draft_id', $draftModel->id)
                ->where('state', 'staging')
                ->get();

            foreach ($stagingAttachments as $attachment) {
                try {
                    $attachmentService->deleteStaging($admin, $attachment);
                } catch (\Throwable $e) {
                    // Abaikan jika berkas sudah tidak ada
                }
            }

            return (bool) $draftModel->delete();
        });
    }
}
