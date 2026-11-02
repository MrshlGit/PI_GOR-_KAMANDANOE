<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lapangan') && ! Schema::hasColumn('lapangan', 'kapasitas')) {
            Schema::table('lapangan', function (Blueprint $table) {
                $table->unsignedTinyInteger('kapasitas')->default(4);
            });
        }

        if (Schema::hasTable('booking') && ! Schema::hasColumn('booking', 'durasi')) {
            Schema::table('booking', function (Blueprint $table) {
                $table->unsignedTinyInteger('durasi')->default(1);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking') && Schema::hasColumn('booking', 'durasi')) {
            Schema::table('booking', function (Blueprint $table) {
                $table->dropColumn('durasi');
            });
        }

        if (Schema::hasTable('lapangan') && Schema::hasColumn('lapangan', 'kapasitas')) {
            Schema::table('lapangan', function (Blueprint $table) {
                $table->dropColumn('kapasitas');
            });
        }
    }
};
