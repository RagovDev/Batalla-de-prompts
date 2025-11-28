<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Obtiene las estadísticas de participación de todos los usuarios.
     */
    public function userStats()
    {
        // Cargamos todos los usuarios junto con sus relaciones para no hacer mil consultas
        // (Eager Loading)
        $users = User::with(['images', 'votes', 'themeVotes'])->get();

        // Transformamos los datos para que sean fáciles de leer en el frontend
        $stats = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                // Pluck('ronda') extrae solo el número de la ronda
                // Unique() elimina duplicados
                // Values() reindexa el array para que sea JSON compatible (ej: [1, 2, 4])
                'uploads' => $user->images->pluck('ronda')->unique()->sort()->values(),
                'votes_images' => $user->votes->pluck('ronda')->unique()->sort()->values(),
                'votes_themes' => $user->themeVotes->pluck('ronda')->unique()->sort()->values(),
            ];
        });

        return response()->json($stats);
    }
}
