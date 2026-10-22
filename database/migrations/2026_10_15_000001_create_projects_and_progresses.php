<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $t) {
            $t->id();
            $t->string('jo_no')->nullable()->unique();
            $t->string('part_no')->nullable()->unique();
            $t->text('part_name')->nullable();
            foreach (['customer', 'model', 'no_doc', 'material', 'spec', 'material_thickness_raw', 'material_width_raw', 'process', 'remark'] as $field) {
                $t->text($field)->nullable();
            }
            foreach (['date_receive', 't0_target', 't0_actual', 'plan_start', 'plan_finish', 'actual_start', 'actual_finish', 'event_1', 'event_2', 'event_3'] as $field) {
                $t->date($field)->nullable();
            }
            $t->boolean('legacy_incomplete')->default(false);
            $t->timestampsTz();
        });
        DB::statement('ALTER TABLE projects ADD CONSTRAINT projects_required CHECK (legacy_incomplete OR (jo_no IS NOT NULL AND part_no IS NOT NULL AND part_name IS NOT NULL AND length(btrim(part_name)) > 0))');
        foreach (['jo_no', 'part_no'] as $field) {
            DB::statement("ALTER TABLE projects ADD CONSTRAINT projects_{$field}_trim CHECK ({$field} IS NULL OR ({$field} = btrim({$field}) AND length({$field}) > 0))");
        }
        Schema::create('progresses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('project_id')->constrained()->restrictOnDelete();
            $t->date('activity_date')->nullable()->index();
            $t->string('activity')->nullable()->index();
            foreach (['description', 'pic_customer', 'process_trial', 'machine', 'die_high_raw', 'material_actual_trial', 'qty_material_raw', 'qty_process_raw', 'qty_sample_raw', 'event_note', 'remark'] as $field) {
                $t->text($field)->nullable();
            }
            $t->string('status')->default('Open')->index();
            $t->unsignedBigInteger('revision')->default(1);
            $t->boolean('legacy_incomplete')->default(false);
            $t->timestampsTz();
            $t->index(['project_id', 'activity_date']);
        });
        DB::statement("ALTER TABLE progresses ADD CONSTRAINT progresses_activity CHECK (activity IS NULL OR activity IN ('Trial', 'Sent Sample', 'Send Document', 'Audit', 'Others'))");
        DB::statement('ALTER TABLE progresses ADD CONSTRAINT progresses_required CHECK (legacy_incomplete OR (activity_date IS NOT NULL AND activity IS NOT NULL))');
        DB::statement("ALTER TABLE progresses ADD CONSTRAINT progresses_status CHECK (status IN ('Open', 'Closed'))");
        DB::statement('ALTER TABLE progresses ADD CONSTRAINT progresses_revision CHECK (revision >= 1)');
    }

    public function down(): void
    {
        Schema::dropIfExists('progresses');
        Schema::dropIfExists('projects');
    }
};
