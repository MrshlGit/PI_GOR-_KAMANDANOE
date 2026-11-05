<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('booking')) {
            return;
        }

        $addNama = ! Schema::hasColumn('booking', 'nama_pemesan');
        $addNoHp = ! Schema::hasColumn('booking', 'no_hp_pemesan');

        if ($addNama || $addNoHp) {
            Schema::table('booking', function (Blueprint $table) use ($addNama, $addNoHp) {
                if ($addNama) {
                    $table->string('nama_pemesan', 100)->nullable();
                }
                if ($addNoHp) {
                    $table->string('no_hp_pemesan', 20)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // Guest details may have existed before this repair migration ran.
    }
};
