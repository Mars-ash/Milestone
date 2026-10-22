<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('material_pitch_raw')->nullable()->after('material_width_raw');
        });

        Schema::table('progresses', function (Blueprint $table) {
            $table->text('qty_ok_raw')->nullable()->after('qty_sample_raw');
            $table->text('qty_ng_raw')->nullable()->after('qty_ok_raw');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('material_pitch_raw');
        });

        Schema::table('progresses', function (Blueprint $table) {
            $table->dropColumn(['qty_ok_raw', 'qty_ng_raw']);
        });
    }
};
