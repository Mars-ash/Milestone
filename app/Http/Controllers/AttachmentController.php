<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Services\ThumbnailService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function __invoke(Attachment $attachment): StreamedResponse
    {
        return $this->download($attachment);
    }

    public function download(Attachment $attachment): StreamedResponse
    {
        Gate::authorize('download', $attachment);
        $disk = Storage::disk(config('uploads.disk', 'private'));
        abort_unless($disk->exists($attachment->storage_key), 404);

        return $disk->download($attachment->storage_key, basename($attachment->original_name), [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function preview(Attachment $attachment): Response
    {
        Gate::authorize('view', $attachment);
        $disk = Storage::disk(config('uploads.disk', 'private'));
        abort_unless($disk->exists($attachment->storage_key), 404);

        $content = $disk->get($attachment->storage_key);

        return response($content, 200, [
            'Content-Type' => $attachment->mime_type ?: 'image/jpeg',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function thumbnail(Attachment $attachment, ThumbnailService $thumbnailService): Response
    {
        Gate::authorize('view', $attachment);
        $disk = Storage::disk(config('uploads.disk', 'private'));
        abort_unless($disk->exists($attachment->storage_key), 404);

        $thumb = $thumbnailService->getThumbnailBytes($attachment);

        return response($thumb, 200, [
            'Content-Type' => function_exists('imagewebp') ? 'image/webp' : ($attachment->mime_type ?: 'image/jpeg'),
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
