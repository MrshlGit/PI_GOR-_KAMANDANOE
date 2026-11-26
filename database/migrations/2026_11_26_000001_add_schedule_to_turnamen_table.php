<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('turnamen')) {
            return;
        }

        Schema::table('turnamen', function (Blueprint $table): void {
            if (! Schema::hasColumn('turnamen', 'jam_mulai')) {
                $table->time('jam_mulai')->nullable();
            }
            if (! Schema::hasColumn('turnamen', 'jam_selesai')) {
                $table->time('jam_selesai')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('turnamen')) {
            return;
        }

        Schema::table('turnamen', function (Blueprint $table): void {
            $columns = array_filter([
                Schema::hasColumn('turnamen', 'jam_mulai') ? 'jam_mulai' : null,
                Schema::hasColumn('turnamen', 'jam_selesai') ? 'jam_selesai' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
