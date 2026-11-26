<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('turnamen') && ! Schema::hasColumn('turnamen', 'poster')) {
            Schema::table('turnamen', function (Blueprint $table) {
                $table->string('poster')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('turnamen') && Schema::hasColumn('turnamen', 'poster')) {
            Schema::table('turnamen', function (Blueprint $table) {
                $table->dropColumn('poster');
            });
        }
    }
};
