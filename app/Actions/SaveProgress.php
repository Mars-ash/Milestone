<?php

namespace App\Actions;

use App\Data\ProgressPayload;
use App\Data\ProgressResult;
use App\Exceptions\FeatureNotReady;
use App\Exceptions\VersionConflict;
use App\Models\Attachment;
use App\Models\CorrectiveAction;
use App\Models\Feedback;
use App\Models\NextAction;
use App\Models\Problem;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\User;
use App\Services\AttachmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class SaveProgress
{
    /**
     * Menerapkan draft graph final secara transaksional (Fase 5).
     */
    public function handle(User $admin, string $draftId, int $expectedDraftRevision): ProgressResult
    {
        return app(ApplyProgressDraft::class)->handle($admin, $draftId, $expectedDraftRevision);
    }

    /**
     * Menyimpan progress beserta entitas anak yang telah aktif secara transaksional.
     */
    public function saveBasic(User $admin, array $input, ?Progress $progress = null, ?ProgressDraft $draft = null): Progress
    {
        return DB::transaction(function () use ($admin, $input, $progress, $draft) {
            $locked = null;

            if ($progress) {
                Gate::forUser($admin)->authorize('update', $progress);

                $locked = Progress::lockForUpdate()->findOrFail($progress->id);
                if ($locked->status === 'Closed') {
                    throw ValidationException::withMessages([
                        'status' => 'Progress berstatus Closed tidak dapat diedit langsung. Silakan Reopen terlebih dahulu.',
                    ]);
                }
            } else {
                Gate::forUser($admin)->authorize('create', Progress::class);
            }

            if ($draft === null) {
                $draft = ProgressDraft::where('admin_id', $admin->id)
                    ->where('state', 'active')
                    ->where('progress_id', $locked?->id)
                    ->first();
            }

            $payload = ProgressPayload::fromArray($input, true, $locked, $draft, $admin);

            $scalarKeys = ['project_id', 'activity_date', 'activity', ...ProgressPayload::TEXT_FIELDS];
            $scalarData = array_intersect_key($payload->data, array_flip($scalarKeys));

            if ($locked) {
                if ($locked->legacy_incomplete) {
                    $scalarData['legacy_incomplete'] = false;
                }

                $locked->update($scalarData);
                $targetProgress = $locked;
            } else {
                $scalarData['status'] = 'Open';
                $scalarData['revision'] = 1;

                $targetProgress = Progress::create($scalarData);
            }

            if (array_key_exists('problems', $payload->data)) {
                $this->syncProblems($targetProgress, $payload->data['problems']);
            }

            if (array_key_exists('next_actions', $payload->data)) {
                $this->syncNextActions($targetProgress, $payload->data['next_actions']);
            }

            if (array_key_exists('feedbacks', $payload->data)) {
                $this->syncFeedbacks($targetProgress, $payload->data['feedbacks']);
            }

            if (! empty($payload->data['attachment_refs'])) {
                $this->syncProgressAttachments($targetProgress, $payload->data['attachment_refs']);
            }

            return $targetProgress->fresh([
                'project',
                'problems.correctiveActions',
                'problems.attachments',
                'nextActions',
                'feedbacks.attachments',
                'feedbacks.legacyDocuments',
                'attachments',
                'legacyDocuments',
            ]);
        });
    }

    /**
     * Upsert daftar problem tanpa mengganti identitas baris tersimpan dan tanpa menghapus record lama.
     */
    private function syncProblems(Progress $progress, array $problems): void
    {
        foreach ($problems as $position => $probData) {
            $attributes = [
                'description' => $probData['description'] ?? null,
                'position' => $position,
            ];

            if (! empty($probData['id'])) {
                $problem = Problem::whereKey($probData['id'])
                    ->where('progress_id', $progress->id)
                    ->firstOrFail();
                $problem->update($attributes);
            } else {
                $problem = Problem::create([
                    'progress_id' => $progress->id,
                    'client_id' => strtolower($probData['client_id']),
                    ...$attributes,
                ]);
            }

            if (isset($probData['corrective_actions']) && is_array($probData['corrective_actions'])) {
                $this->syncCorrectiveActions($problem, $probData['corrective_actions']);
            }

            if (! empty($probData['photo_refs'])) {
                foreach ($probData['photo_refs'] as $attId) {
                    $att = Attachment::whereKey($attId)->first();
                    if ($att && $att->state === 'staging') {
                        app(\App\Services\AttachmentService::class)->finalizeAttachment($att, $progress, problemId: $problem->id);
                    }
                }
            }
        }
    }

    /**
     * Upsert daftar corrective action per problem tanpa mengganti identitas tersimpan.
     */
    private function syncCorrectiveActions(Problem $problem, array $actions): void
    {
        foreach ($actions as $pos => $actData) {
            $attributes = [
                'description' => $actData['description'] ?? null,
                'pic' => $actData['pic'] ?? null,
                'actual_date' => $actData['actual_date'] ?? null,
                'position' => $pos,
            ];

            if (! empty($actData['id'])) {
                CorrectiveAction::whereKey($actData['id'])
                    ->where('problem_id', $problem->id)
                    ->update($attributes);
            } else {
                CorrectiveAction::create([
                    'problem_id' => $problem->id,
                    'client_id' => strtolower($actData['client_id']),
                    ...$attributes,
                ]);
            }
        }
    }

    /**
     * Upsert daftar next action pada progress tanpa mengganti identitas baris tersimpan.
     */
    private function syncNextActions(Progress $progress, array $actions): void
    {
        foreach ($actions as $pos => $actData) {
            $attributes = [
                'description' => $actData['description'] ?? null,
                'pic' => $actData['pic'] ?? null,
                'actual_date' => $actData['actual_date'] ?? null,
                'position' => $pos,
            ];

            if (! empty($actData['id'])) {
                NextAction::whereKey($actData['id'])
                    ->where('progress_id', $progress->id)
                    ->update($attributes);
            } else {
                NextAction::create([
                    'progress_id' => $progress->id,
                    'client_id' => strtolower($actData['client_id']),
                    ...$attributes,
                ]);
            }
        }
    }

    /**
     * Upsert daftar feedback pada progress tanpa mengganti identitas baris tersimpan.
     */
    private function syncFeedbacks(Progress $progress, array $feedbacks): void
    {
        foreach ($feedbacks as $pos => $fbData) {
            $attributes = [
                'content' => $fbData['content'] ?? null,
                'pic' => $fbData['pic'] ?? null,
                'feedback_date' => $fbData['feedback_date'] ?? null,
                'position' => $pos,
            ];

            if (! empty($fbData['id'])) {
                $feedback = Feedback::whereKey($fbData['id'])
                    ->where('progress_id', $progress->id)
                    ->firstOrFail();
                $feedback->update($attributes);
            } else {
                $feedback = Feedback::create([
                    'progress_id' => $progress->id,
                    'client_id' => strtolower($fbData['client_id']),
                    ...$attributes,
                ]);
            }

            if (! empty($fbData['document_refs'])) {
                foreach ($fbData['document_refs'] as $attId) {
                    $att = Attachment::whereKey($attId)->first();
                    if ($att && $att->state === 'staging') {
                        app(AttachmentService::class)->finalizeAttachment($att, $progress, feedbackId: $feedback->id);
                    }
                }
            }
        }
    }

    /**
     * Finalisasi lampiran dokumen langsung pada progress.
     */
    private function syncProgressAttachments(Progress $progress, array $attachmentRefs): void
    {
        foreach ($attachmentRefs as $attId) {
            $att = Attachment::whereKey($attId)->first();
            if ($att && $att->state === 'staging') {
                app(AttachmentService::class)->finalizeAttachment($att, $progress);
            }
        }
    }
}
