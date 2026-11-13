<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('makanans', function (Blueprint $table) {
            $table->id('id_makanan');
            $table->string('nama', 150);
            $table->text('deskripsi')->nullable();
            $table->decimal('harga_sewa', 12, 2);
            $table->unsignedInteger('stok')->default(0);
            $table->string('satuan', 50)->default('sesi');
            $table->string('gambar')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('makanans');
    }
};
