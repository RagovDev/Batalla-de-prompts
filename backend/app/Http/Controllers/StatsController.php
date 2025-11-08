<?php

namespace App\Http\Controllers;

use App\Models\User;      // <-- Importa el modelo User
use App\Models\Vote;      // <-- Importa el modelo Vote
use App\Models\ThemeVote; // <-- Importa el modelo ThemeVote
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Devuelve estadísticas globales de la aplicación.
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // 1. Contar participantes (usuarios)
            $totalParticipants = User::count();

            // 2. Contar votos de imágenes
            $totalImageVotes = Vote::count();

            // 3. Contar votos de temas
            $totalThemeVotes = ThemeVote::count();

            // 4. Devolver la respuesta JSON
            return response()->json([
                'totalParticipants' => $totalParticipants,
                'totalImageVotes' => $totalImageVotes,
                'totalThemeVotes' => $totalThemeVotes
            ]);

        } catch (\Exception $e) {
            // Manejar cualquier error de base de datos
            return response()->json(['message' => 'Error al obtener estadísticas: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Devuelve el total de votos para temas en una ronda específica.
     */
    public function themeVotesByRound($ronda)
    {
        try {
            // Filtra por 'ronda' y luego cuenta
            $count = ThemeVote::where('ronda', $ronda)->count();

            return response()->json([
                'round' => (int)$ronda,
                'totalVotesInRound' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener votos de temas: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Devuelve el total de votos para imágenes en una ronda específica.
     */
    public function imageVotesByRound($ronda)
    {
        try {
            // Filtra por 'ronda' y luego cuenta
            $count = Vote::where('ronda', $ronda)->count();

            return response()->json([
                'round' => (int)$ronda,
                'totalVotesInRound' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener votos de imágenes: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
