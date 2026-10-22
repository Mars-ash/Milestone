<?php

namespace App\Providers;

use App\Models\Attachment;
use App\Models\CorrectiveAction;
use App\Models\Feedback;
use App\Models\LegacyDocumentMetadata;
use App\Models\NextAction;
use App\Models\Problem;
use App\Models\Progress;
use App\Models\ProgressDraft;
use App\Models\Project;
use App\Policies\AdminPolicy;
use App\Policies\AttachmentPolicy;
use App\Policies\ProgressDraftPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (request()->server('HTTP_X_FORWARDED_PROTO') === 'https' || request()->isSecure()) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        foreach ([Project::class, Progress::class, Problem::class, CorrectiveAction::class, NextAction::class, Feedback::class, LegacyDocumentMetadata::class] as $model) {
            Gate::policy($model, AdminPolicy::class);
        }
        Gate::policy(ProgressDraft::class, ProgressDraftPolicy::class);
        Gate::policy(Attachment::class, AttachmentPolicy::class);
    }
}
