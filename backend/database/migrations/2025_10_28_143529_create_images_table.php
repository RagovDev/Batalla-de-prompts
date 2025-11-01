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
        Schema::create('images', function (Blueprint $table) {
            $table->id(); // ID autoincremental
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relación con usuario
            $table->string('filename');
            $table->string('image_url');
            $table->integer('ronda');
            $table->integer('votes')->default(0);
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
