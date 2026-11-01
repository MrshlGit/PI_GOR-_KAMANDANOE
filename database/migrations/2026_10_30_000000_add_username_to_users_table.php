<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user')) {
            return;
        }

        if (!Schema::hasColumn('user', 'username')) {
            Schema::table('user', function (Blueprint $table) {
                $table->string('username')->nullable()->unique()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('user') || !Schema::hasColumn('user', 'username')) {
            return;
        }

        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
