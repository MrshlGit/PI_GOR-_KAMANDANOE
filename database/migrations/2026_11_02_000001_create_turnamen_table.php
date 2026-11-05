<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('turnamen')) {
            return;
        }

        Schema::create('turnamen', function (Blueprint $table) {
            $table->string('id_turnamen', 20)->primary();
            $table->integer('id_user')->nullable()->index();
            $table->decimal('biaya_pendaftaran', 12, 2)->nullable();
            $table->string('nama_turnamen', 100)->nullable();
            $table->date('tgl_pelaksanaan')->nullable();
            $table->foreign('id_user')
                ->references('id')
                ->on('user')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnamen');
    }
};
