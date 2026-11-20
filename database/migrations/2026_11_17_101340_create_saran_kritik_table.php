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
        if (! Schema::hasTable('saran_kritik')) {
            Schema::create('saran_kritik', function (Blueprint $table) {
                $table->string('id', 20)->primary();
                $table->unsignedBigInteger('id_user')->nullable();
                $table->string('nama', 100)->nullable();
                $table->text('isi');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saran_kritik');
    }
};
