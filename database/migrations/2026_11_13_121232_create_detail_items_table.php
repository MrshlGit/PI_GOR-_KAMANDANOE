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
        Schema::create('detail_items', function (Blueprint $table) {
            $table->id('id_detail_item');

            $table->foreignId('id_pesanan')
                ->constrained('pesanan', 'id_pesanan')
                ->cascadeOnDelete();

            $table->foreignId('id_alat')
                ->nullable()
                ->constrained('alat', 'id_alat')
                ->restrictOnDelete();

            $table->foreignId('id_makanan')
                ->nullable()
                ->constrained('makanan', 'id_makanan')
                ->restrictOnDelete();

            $table->string('nama_item', 150);
            $table->unsignedInteger('jumlah');
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_items');
    }
};
