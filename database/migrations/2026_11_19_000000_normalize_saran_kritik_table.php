<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('saran_kritik')) {
            return;
        }

        foreach ([
            'id_saran' => 'id',
            'tgl_kirim' => 'created_at',
            'isi_pesan' => 'isi',
        ] as $oldName => $newName) {
            if (! Schema::hasColumn('saran_kritik', $newName)
                && Schema::hasColumn('saran_kritik', $oldName)) {
                Schema::table('saran_kritik', function (Blueprint $table) use ($oldName, $newName): void {
                    $table->renameColumn($oldName, $newName);
                });
            }
        }

        Schema::table('saran_kritik', function (Blueprint $table): void {
            if (! Schema::hasColumn('saran_kritik', 'nama')) {
                $table->string('nama', 100)->nullable();
            }

            if (! Schema::hasColumn('saran_kritik', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('saran_kritik')) {
            return;
        }

        $columnsToDrop = array_values(array_filter(
            ['nama', 'updated_at'],
            fn (string $column): bool => Schema::hasColumn('saran_kritik', $column)
        ));

        if ($columnsToDrop !== []) {
            Schema::table('saran_kritik', function (Blueprint $table) use ($columnsToDrop): void {
                $table->dropColumn($columnsToDrop);
            });
        }

        foreach ([
            'created_at' => 'tgl_kirim',
            'isi' => 'isi_pesan',
            'id' => 'id_saran',
        ] as $newName => $oldName) {
            if (! Schema::hasColumn('saran_kritik', $oldName)
                && Schema::hasColumn('saran_kritik', $newName)) {
                Schema::table('saran_kritik', function (Blueprint $table) use ($newName, $oldName): void {
                    $table->renameColumn($newName, $oldName);
                });
            }
        }
    }
};
