<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('turnamen') || ! Schema::hasTable('turnamen_peserta')) {
            return;
        }

        $kolom = DB::selectOne(
            "SELECT COLLATION_NAME AS c FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'turnamen' AND COLUMN_NAME = 'id_turnamen'"
        );

        if (! $kolom || ! preg_match('/^[a-z0-9_]+$/', (string) $kolom->c)) {
            return;
        }

        $collation = $kolom->c;
        $charset = explode('_', $collation)[0];

        DB::statement(
            "ALTER TABLE `turnamen_peserta` MODIFY `id_turnamen` VARCHAR(20) CHARACTER SET {$charset} COLLATE {$collation} NOT NULL"
        );
    }

    public function down(): void
    {
        // dibiarkan kosong
    }
};