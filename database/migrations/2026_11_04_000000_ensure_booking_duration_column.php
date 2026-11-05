<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('booking') && ! Schema::hasColumn('booking', 'durasi')) {
            Schema::table('booking', function (Blueprint $table) {
                $table->unsignedTinyInteger('durasi')->default(1);
            });
        }
    }

    public function down(): void
    {
        // Keep this repair migration non-destructive: the column may have existed
        // before this migration ran, so it cannot safely be removed on rollback.
    }
};
