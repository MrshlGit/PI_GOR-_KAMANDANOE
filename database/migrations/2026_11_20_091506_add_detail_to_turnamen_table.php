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

        Schema::table('turnamen', function (Blueprint $table) {
            if (! Schema::hasColumn('turnamen', 'kategori')) {
                $table->string('kategori', 50)->nullable();
            }
            if (! Schema::hasColumn('turnamen', 'lokasi')) {
                $table->string('lokasi', 150)->nullable();
            }
            if (! Schema::hasColumn('turnamen', 'batas_pendaftaran')) {
                $table->date('batas_pendaftaran')->nullable();
            }
            if (! Schema::hasColumn('turnamen', 'kuota_tim')) {
                $table->unsignedSmallInteger('kuota_tim')->default(16);
            }
            if (! Schema::hasColumn('turnamen', 'hadiah')) {
                $table->string('hadiah', 150)->nullable();
            }
            if (! Schema::hasColumn('turnamen', 'kontak')) {
                $table->string('kontak', 30)->nullable();
            }
            if (! Schema::hasColumn('turnamen', 'deskripsi')) {
                $table->text('deskripsi')->nullable();
            }
            // data lama dianggap sudah disetujui; pengajuan baru diisi 'menunggu' oleh controller
            if (! Schema::hasColumn('turnamen', 'status')) {
                $table->string('status', 20)->default('disetujui');
            }
        });
    }

    public function down(): void
    {
        // dibiarkan kosong agar data turnamen lama tidak ikut terhapus
    }
};