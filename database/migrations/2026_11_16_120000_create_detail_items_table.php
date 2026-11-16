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
        if (! Schema::hasTable('detail_items')) {
            Schema::create('detail_items', function (Blueprint $table) {
                $table->id('id_detail_item');

                $table->foreignId('id_pesanan');
                $table->foreignId('id_alat')->nullable();
                $table->foreignId('id_makanan')->nullable();

                $table->string('nama_item', 150);
                $table->unsignedInteger('jumlah');
                $table->decimal('harga_satuan', 12, 2);
                $table->decimal('subtotal', 12, 2);
                $table->timestamps();
            });
        }

        $foreignKeys = Schema::getForeignKeys('detail_items');
        $relations = [
            ['column' => 'id_pesanan', 'table' => 'pesanans', 'on_delete' => 'cascade'],
            ['column' => 'id_alat', 'table' => 'alats', 'on_delete' => 'restrict'],
            ['column' => 'id_makanan', 'table' => 'makanans', 'on_delete' => 'restrict'],
        ];

        foreach ($relations as $relation) {
            $exists = false;

            foreach ($foreignKeys as $foreignKey) {
                if (
                    $foreignKey['columns'] === [$relation['column']]
                    && $foreignKey['foreign_table'] === $relation['table']
                    && $foreignKey['foreign_columns'] === [$relation['column']]
                ) {
                    $exists = true;
                    break;
                }
            }

            if ($exists) {
                continue;
            }

            Schema::table('detail_items', function (Blueprint $table) use ($relation) {
                $foreignKey = $table->foreign($relation['column'])
                    ->references($relation['column'])
                    ->on($relation['table']);

                if ($relation['on_delete'] === 'cascade') {
                    $foreignKey->cascadeOnDelete();
                } else {
                    $foreignKey->restrictOnDelete();
                }
            });

            $foreignKeys[] = [
                'columns' => [$relation['column']],
                'foreign_table' => $relation['table'],
                'foreign_columns' => [$relation['column']],
            ];
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_items');
    }
};
