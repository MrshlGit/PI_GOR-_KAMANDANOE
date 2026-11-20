<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('turnamen_peserta')) {
            return;
        }

        Schema::create('turnamen_peserta', function (Blueprint $table) {
            $table->id();
            $table->string('id_turnamen', 20)->index();
            $table->unsignedBigInteger('id_user')->index();
            $table->string('nama_tim', 100);
            $table->string('kontak', 30);
            $table->timestamps();

            // satu akun hanya boleh mendaftar sekali di satu turnamen
            $table->unique(['id_turnamen', 'id_user']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnamen_peserta');
    }
};