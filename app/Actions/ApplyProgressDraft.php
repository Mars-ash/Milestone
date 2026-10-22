<?php

namespace App\Actions;

use App\Data\ProgressResult;
use App\Exceptions\VersionConflict;
use App\Models\Attachment;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\User;
use App\Services\AttachmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class ApplyProgressDraft
{
    public function __construct(
        private readonly SaveProgress $saveProgress
    ) {}

    /**
     * Menerapkan draft ke data progress operasional secara idempoten dalam satu transaksi database.
     *
     * @throws VersionConflict
     */
    public function handle(User $admin, string $draftId, ?int $expectedDraftRevision = null): ProgressResult
    {
        return DB::transaction(function () use ($admin, $draftId, $expectedDraftRevision) {
            $draft = ProgressDraft::where('id', $draftId)
                ->where('admin_id', $admin->id)
                ->lockForUpdate()
                ->firstOrFail();

            Gate::forUser($admin)->authorize('view', $draft);

            // Replay Idempotency: Jika draft sudah diterapkan sebelumnya, kembalikan progress yang ada tanpa menduplikasi data anak (AT14/AT15/AT16)
            if ($draft->state === 'applied') {
                $appliedProgress = Progress::with([
                    'project',
                    'problems.correctiveActions',
                    'problems.attachments',
                    'nextActions',
                    'feedbacks.attachments',
                    'feedbacks.legacyDocuments',
                    'attachments',
                    'legacyDocuments',
                ])->find($draft->applied_progress_id);

                return new ProgressResult($appliedProgress, 'already_applied', 'Draft sudah diterapkan.');
            }

            Gate::forUser($admin)->authorize('update', $draft);

            // Cek konflik versi draft jika ditentukan
            if ($expectedDraftRevision !== null && (int) $draft->draft_revision !== (int) $expectedDraftRevision) {
                throw new VersionConflict();
            }

            $progress = null;
            if ($draft->progress_id !== null) {
                $progress = Progress::lockForUpdate()->find($draft->progress_id);
                if ($progress) {
                    if ($draft->base_revision !== null && (int) $progress->revision !== (int) $draft->base_revision) {
                        throw new VersionConflict();
                    }
                    Gate::forUser($admin)->authorize('update', $progress);
                }
            }

            // Terapkan payload draft ke tabel progress & entitas anak melalui saveBasic
            $savedProgress = $this->saveProgress->saveBasic(
                admin: $admin,
                input: $draft->payload,
                progress: $progress,
                draft: $draft
            );

            // Tingkatkan revision jika mengedit progress yang sudah ada
            if ($progress) {
                $progress->increment('revision');
                $savedProgress = $progress->fresh();
            }

            // Update status draft menjadi applied dan simpan applied_progress_id
            $draft->update([
                'state' => 'applied',
                'applied_progress_id' => $savedProgress->id,
            ]);

            // Bersihkan sisa staging files yang tidak digunakan
            $remainingStaging = Attachment::where('draft_id', $draft->id)
                ->where('state', 'staging')
                ->get();

            foreach ($remainingStaging as $st) {
                try {
                    app(AttachmentService::class)->deleteStaging($admin, $st);
                } catch (\Throwable $e) {
                    // abaikan jika berkas sudah tidak ada
                }
            }

            return new ProgressResult($savedProgress, 'saved', 'Progress berhasil disimpan.');
        });
    }
}
