<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user')) {
            return;
        }

        Schema::create('user', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('username', 100)->nullable();
            $table->string('noHp', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'user'])->nullable();
            $table->primary('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
