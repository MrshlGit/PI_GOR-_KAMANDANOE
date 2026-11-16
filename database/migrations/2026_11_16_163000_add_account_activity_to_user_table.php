<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user')) {
            return;
        }

        $columns = [];
        if (! Schema::hasColumn('user', 'created_at')) {
            $columns[] = 'created_at';
        }
        if (! Schema::hasColumn('user', 'last_online_at')) {
            $columns[] = 'last_online_at';
        }

        if ($columns === []) {
            return;
        }

        Schema::table('user', function (Blueprint $table) use ($columns): void {
            foreach ($columns as $column) {
                $table->timestamp($column)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('user')) {
            return;
        }

        $columns = array_values(array_filter(
            ['created_at', 'last_online_at'],
            fn (string $column): bool => Schema::hasColumn('user', $column)
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('user', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }
};
