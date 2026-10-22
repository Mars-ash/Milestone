<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttachmentService
{
    public function getDisk(): Filesystem
    {
        return Storage::disk(config('uploads.disk', 'private'));
    }

    /**
     * Upload dan tempatkan file pada staging disk privat dengan validasi ketat.
     *
     * @throws ValidationException
     */
    public function stage(
        User $admin,
        ProgressDraft $draft,
        UploadedFile $file,
        string $kind,
        string $ownerKind,
        ?string $ownerClientId = null,
        ?string $caption = null
    ): Attachment {
        if (! in_array($kind, ['photo', 'document'], true)) {
            throw ValidationException::withMessages([
                'kind' => 'Tipe lampiran tidak valid (harus photo atau document).',
            ]);
        }

        if (! in_array($ownerKind, ['progress', 'problem', 'feedback'], true)) {
            throw ValidationException::withMessages([
                'owner_kind' => 'Pemilik lampiran tidak valid.',
            ]);
        }

        if ($ownerKind === 'progress' && $ownerClientId !== null) {
            throw ValidationException::withMessages([
                'owner_client_id' => 'Lampiran langsung pada progress tidak boleh memiliki owner_client_id.',
            ]);
        }

        if (in_array($ownerKind, ['problem', 'feedback'], true)) {
            if (empty($ownerClientId) || ! Str::isUuid($ownerClientId)) {
                throw ValidationException::withMessages([
                    'owner_client_id' => 'owner_client_id harus berupa UUID valid.',
                ]);
            }
        }

        if ((int) $draft->admin_id !== (int) $admin->id || $draft->state !== 'active') {
            throw ValidationException::withMessages([
                'draft_id' => 'Draft tidak aktif atau bukan milik admin yang sedang login.',
            ]);
        }

        $this->validateFile($file, $kind);

        $storageKey = 'attachments/'.(string) Str::uuid();
        $checksum = hash_file('sha256', $file->getRealPath());
        $disk = $this->getDisk();

        $content = file_get_contents($file->getRealPath());
        if ($content === false) {
            throw ValidationException::withMessages([
                'file' => 'Gagal membaca isi berkas unggahan.',
            ]);
        }

        $disk->put($storageKey, $content);

        try {
            return DB::transaction(function () use ($admin, $draft, $file, $kind, $ownerKind, $ownerClientId, $caption, $storageKey, $checksum) {
                return Attachment::create([
                    'id' => (string) Str::uuid(),
                    'uploaded_by' => $admin->id,
                    'draft_id' => $draft->id,
                    'progress_id' => null,
                    'problem_id' => null,
                    'feedback_id' => null,
                    'owner_kind' => $ownerKind,
                    'owner_client_id' => $ownerClientId ? strtolower($ownerClientId) : null,
                    'state' => 'staging',
                    'kind' => $kind,
                    'storage_key' => $storageKey,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'size_bytes' => $file->getSize(),
                    'checksum' => $checksum,
                    'caption' => $caption,
                ]);
            });
        } catch (\Throwable $e) {
            $disk->delete($storageKey);
            throw $e;
        }
    }

    /**
     * Validasi berkas fisik, ekstensi, MIME asli, dan integritas format.
     */
    public function validateFile(UploadedFile $file, string $kind): void
    {
        $configKey = $kind === 'photo' ? 'uploads.photos' : 'uploads.documents';
        $maxKb = (int) config("{$configKey}.max_size_kb", 5120);
        $allowedExts = config("{$configKey}.allowed_extensions", []);
        $allowedMimes = config("{$configKey}.allowed_mimes", []);

        $fileSizeKb = $file->getSize() / 1024;
        if ($fileSizeKb > $maxKb) {
            throw ValidationException::withMessages([
                'file' => "Ukuran file melebihi batas maksimum {$maxKb} KB.",
            ]);
        }

        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, $allowedExts, true)) {
            throw ValidationException::withMessages([
                'file' => "Ekstensi file .{$ext} tidak diizinkan untuk {$kind}.",
            ]);
        }

        $mime = $file->getMimeType() ?: '';
        if (! in_array($mime, $allowedMimes, true)) {
            throw ValidationException::withMessages([
                'file' => "MIME type {$mime} tidak didukung untuk {$kind}.",
            ]);
        }

        // Untuk foto: verifikasi berkas benar-benar berisi gambar valid
        if ($kind === 'photo') {
            $imageInfo = @getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                throw ValidationException::withMessages([
                    'file' => 'Berkas bukan gambar yang valid meskipun ber-ekstensi gambar.',
                ]);
            }

            $detectedMime = $imageInfo['mime'] ?? '';
            if (! in_array($detectedMime, $allowedMimes, true)) {
                throw ValidationException::withMessages([
                    'file' => "Isi gambar terdeteksi sebagai {$detectedMime}, tidak sesuai format foto yang diizinkan.",
                ]);
            }
        }
    }

    /**
     * Hapus berkas staging milik admin dari disk privat dan basis data.
     */
    public function deleteStaging(User $admin, Attachment $attachment): void
    {
        if ((int) $attachment->uploaded_by !== (int) $admin->id || $attachment->state !== 'staging') {
            throw ValidationException::withMessages([
                'attachment' => 'Hanya lampiran berstatus staging milik admin bersangkutan yang dapat dihapus.',
            ]);
        }

        $disk = $this->getDisk();
        if ($disk->exists($attachment->storage_key)) {
            $disk->delete($attachment->storage_key);
        }

        $attachment->delete();
    }

    /**
     * Finalisasi lampiran dari staging ke attached pada entitas tujuan.
     */
    public function finalizeAttachment(
        Attachment $attachment,
        Progress $progress,
        ?int $problemId = null,
        ?int $feedbackId = null
    ): void {
        if ($attachment->state !== 'staging') {
            return;
        }

        $attributes = [
            'state' => 'attached',
            'draft_id' => null,
            'owner_client_id' => null,
        ];

        if ($attachment->owner_kind === 'progress') {
            $attributes['progress_id'] = $progress->id;
            $attributes['problem_id'] = null;
            $attributes['feedback_id'] = null;
        } elseif ($attachment->owner_kind === 'problem') {
            if (! $problemId) {
                throw new \InvalidArgumentException('Problem ID wajib untuk finalisasi lampiran problem.');
            }
            $attributes['progress_id'] = null;
            $attributes['problem_id'] = $problemId;
            $attributes['feedback_id'] = null;
        } elseif ($attachment->owner_kind === 'feedback') {
            if (! $feedbackId) {
                throw new \InvalidArgumentException('Feedback ID wajib untuk finalisasi lampiran feedback.');
            }
            $attributes['progress_id'] = null;
            $attributes['problem_id'] = null;
            $attributes['feedback_id'] = $feedbackId;
        }

        $attachment->update($attributes);
    }
}
