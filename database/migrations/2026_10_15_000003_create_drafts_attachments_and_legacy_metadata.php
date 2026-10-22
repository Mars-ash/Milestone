<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_drafts', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignId('admin_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('progress_id')->nullable()->constrained('progresses')->restrictOnDelete();
            $t->unsignedBigInteger('base_revision')->nullable();
            $t->unsignedBigInteger('draft_revision')->default(1);
            $t->unsignedBigInteger('request_sequence')->default(1);
            $t->unsignedInteger('schema_version')->default(1);
            $t->jsonb('payload');
            $t->string('state')->default('active');
            $t->foreignId('applied_progress_id')->nullable()->constrained('progresses')->restrictOnDelete();
            $t->timestampsTz();
            $t->index(['admin_id', 'state']);
        });
        DB::statement("CREATE UNIQUE INDEX one_active_edit_draft ON progress_drafts (admin_id, progress_id) WHERE state = 'active' AND progress_id IS NOT NULL");
        DB::statement("ALTER TABLE progress_drafts ADD CONSTRAINT draft_state CHECK ((state = 'active' AND applied_progress_id IS NULL) OR (state = 'applied' AND applied_progress_id IS NOT NULL))");
        DB::statement('ALTER TABLE progress_drafts ADD CONSTRAINT draft_base CHECK ((progress_id IS NULL AND base_revision IS NULL) OR (progress_id IS NOT NULL AND base_revision >= 1 AND base_revision IS NOT NULL))');
        DB::statement('ALTER TABLE progress_drafts ADD CONSTRAINT draft_applied_target CHECK (progress_id IS NULL OR applied_progress_id IS NULL OR progress_id = applied_progress_id)');
        DB::statement('ALTER TABLE progress_drafts ADD CONSTRAINT draft_revision_positive CHECK (draft_revision >= 1 AND request_sequence >= 1 AND schema_version >= 1)');
        DB::statement("ALTER TABLE progress_drafts ADD CONSTRAINT draft_payload_object CHECK (jsonb_typeof(payload) = 'object')");
        Schema::create('attachments', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $t->foreignUuid('draft_id')->nullable()->constrained('progress_drafts')->restrictOnDelete();
            $t->foreignId('progress_id')->nullable()->constrained('progresses')->restrictOnDelete();
            $t->foreignId('problem_id')->nullable()->constrained()->restrictOnDelete();
            $t->foreignId('feedback_id')->nullable()->constrained('feedbacks')->restrictOnDelete();
            $t->uuid('owner_client_id')->nullable();
            $t->string('owner_kind');
            $t->string('state')->default('staging');
            $t->string('kind');
            $t->string('storage_key')->unique();
            $t->text('original_name');
            $t->string('mime_type');
            $t->unsignedBigInteger('size_bytes');
            $t->char('checksum', 64);
            $t->text('caption')->nullable();
            $t->timestampsTz();
        });
        DB::statement("ALTER TABLE attachments ADD CONSTRAINT attachment_owner CHECK (num_nonnulls(draft_id, progress_id, problem_id, feedback_id) = 1 AND ((state = 'staging' AND draft_id IS NOT NULL AND ((owner_kind = 'progress' AND owner_client_id IS NULL) OR (owner_kind IN ('problem', 'feedback') AND owner_client_id IS NOT NULL))) OR (state = 'attached' AND draft_id IS NULL AND owner_client_id IS NULL AND ((owner_kind = 'progress' AND progress_id IS NOT NULL) OR (owner_kind = 'problem' AND problem_id IS NOT NULL) OR (owner_kind = 'feedback' AND feedback_id IS NOT NULL)))))");
        DB::statement("ALTER TABLE attachments ADD CONSTRAINT attachment_metadata CHECK (kind IN ('photo', 'document') AND size_bytes > 0 AND checksum ~ '^[0-9a-f]{64}$' AND storage_key ~ '^attachments/[0-9a-f-]{36}$')");
        Schema::create('legacy_document_metadata', function (Blueprint $t) {
            $t->id();
            $t->foreignId('progress_id')->nullable()->constrained('progresses')->restrictOnDelete();
            $t->foreignId('feedback_id')->nullable()->constrained('feedbacks')->restrictOnDelete();
            $t->string('source_field');
            $t->text('name')->nullable();
            $t->text('description')->nullable();
            $t->timestampsTz();
        });
        DB::statement('ALTER TABLE legacy_document_metadata ADD CONSTRAINT legacy_single_owner CHECK (num_nonnulls(progress_id, feedback_id) = 1)');
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_document_metadata');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('progress_drafts');
    }
};
