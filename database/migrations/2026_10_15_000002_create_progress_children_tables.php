<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['problems', 'corrective_actions', 'next_actions', 'feedbacks'] as $name) {
            Schema::create($name, function (Blueprint $t) use ($name) {
                $t->id();
                $parent = $name === 'corrective_actions' ? 'problem_id' : 'progress_id';
                $t->foreignId($parent)->constrained($name === 'corrective_actions' ? 'problems' : 'progresses')->restrictOnDelete();
                $t->uuid('client_id');
                $t->unsignedInteger('position')->default(0);
                $t->text($name === 'feedbacks' ? 'content' : 'description')->nullable();
                if ($name !== 'problems') {
                    $t->text('pic')->nullable();
                    $t->date($name === 'feedbacks' ? 'feedback_date' : 'actual_date')->nullable();
                }
                $t->timestampsTz();
                $t->unique([$parent, 'client_id']);
                $t->index([$parent, 'position']);
            });
            DB::statement("ALTER TABLE {$name} ADD CONSTRAINT {$name}_position CHECK (position >= 0)");
        }
    }

    public function down(): void
    {
        foreach (['feedbacks', 'next_actions', 'corrective_actions', 'problems'] as $name) {
            Schema::dropIfExists($name);
        }
    }
};
