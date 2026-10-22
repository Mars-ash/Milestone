<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progresses', function (Blueprint $t) {
            $t->text('tonage_raw')->nullable()->after('machine');
        });
    }

    public function down(): void
    {
        Schema::table('progresses', function (Blueprint $t) {
            $t->dropColumn('tonage_raw');
        });
    }
};
