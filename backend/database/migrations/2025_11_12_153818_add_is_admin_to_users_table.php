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
        Schema::table('users', function (Blueprint $table) {
            // Añadimos una columna 'is_admin' de tipo booleano (true/false)
            // Se coloca después de la columna 'password' (opcional, por orden)
            // Por defecto, todos los nuevos usuarios NO serán administradores (default(false))
            $table->boolean('is_admin')->default(false)->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Esto permite deshacer la migración si es necesario
            $table->dropColumn('is_admin');
        });
    }
};
