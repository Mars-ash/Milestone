<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_records', function (Blueprint $table) {
            $table->id();
            $table->string('source_table')->index();
            $table->string('source_id')->index();
            $table->string('target_table')->index();
            $table->unsignedBigInteger('target_id')->index();
            $table->string('checksum')->nullable();
            $table->string('status')->default('imported')->index();
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->unique(['source_table', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_records');
    }
};
