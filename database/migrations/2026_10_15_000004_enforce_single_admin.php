<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fails safely if an existing installation contains more than one account.
        Schema::table('users', fn (Blueprint $t) => $t->boolean('admin_slot')->default(true)->unique());
        DB::statement('ALTER TABLE users ADD CONSTRAINT only_admin CHECK (admin_slot = true)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT only_admin');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('admin_slot'));
    }
};
