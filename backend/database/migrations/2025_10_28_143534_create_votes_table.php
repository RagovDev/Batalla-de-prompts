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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Quién votó
            $table->foreignId('image_id')->constrained()->onDelete('cascade'); // Por qué imagen votó
            $table->integer('ronda');
            $table->timestamps();
            $table->unique(['user_id', 'ronda']); // Asegura un voto por ronda
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
