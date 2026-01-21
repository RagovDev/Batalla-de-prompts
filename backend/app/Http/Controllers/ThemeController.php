<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use App\Models\ThemeVote; // <-- Importa el modelo de votos de temas
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Importa Auth para el usuario
use Illuminate\Support\Facades\Storage; // <-- Importa Storage para las URLs
use Illuminate\Database\QueryException; // <-- Importa para manejar errores de BD

class ThemeController extends Controller
{
    /**
     * Muestra los temas de una ronda y el estado de votación del usuario.
     * Display a listing of the resource.
     */
    public function index($ronda)
    {
        // 1. Validar la ronda
        if (!is_numeric($ronda) || $ronda < 1 || $ronda > 4) {
             return response()->json(['message' => 'Ronda inválida'], 400);
        }

        // 2. Obtener el usuario autenticado
        $user = Auth::user();

        // 3. Obtener los temas de la ronda
        $themes = Theme::where('ronda', $ronda)->get();

        // 4. Formatear los temas para incluir la URL completa
        $formattedThemes = $themes->map(function ($theme) {
            return [
                'id' => $theme->id,
                'ronda' => $theme->ronda,
                'title' => $theme->title,
                // Asume que las imágenes de temas están en 'storage/app/public/themes/'
                'imageUrl' => $theme->image_url ? Storage::url($theme->image_url) : null,
                'votes' => $theme->votes,
            ];
        });

        // 5. Verificar si el usuario ya votó en esta ronda (igual que en Node.js)
        $existingVote = ThemeVote::where('user_id', $user->id)
                                 ->where('ronda', $ronda)
                                 ->first();

        // 6. Devolver la respuesta JSON (mismo formato que el frontend espera)
        return response()->json([
            'temas' => $formattedThemes,
            'haVotado' => !!$existingVote, // Convierte el resultado en booleano (true/false)
            'userVotedFor' => $existingVote ? $existingVote->theme_id : null
        ]);
    }

    /**
     * Registra el voto de un usuario para un tema.
     */
    public function vote(Request $request)
    {
        // 1. Validar la entrada
        $request->validate([
            'themeId' => 'required|integer|exists:themes,id'
        ]);

        // 2. Obtener el tema y el usuario
        $theme = Theme::findOrFail($request->themeId);
        $user = Auth::user();

        // 3. Regla: Usar la base de datos para manejar el voto duplicado
        // Esto aprovecha la restricción 'unique' que pusimos en la migración
        try {
            ThemeVote::create([
                'user_id' => $user->id,
                'theme_id' => $theme->id,
                'ronda' => $theme->ronda
            ]);

            // 4. Incrementar el contador de votos en el tema
            $theme->increment('votes');

            return response()->json([
                'success' => true, 
                'message' => '¡Gracias por tu voto!'
            ], 201); // 201 Created

        } catch (QueryException $e) {
            // 5. Manejar el error de voto duplicado
            // '23000' es el código SQL para violación de integridad (como una 'unique constraint')
            if ($e->getCode() == '23000') {
                return response()->json(['message' => 'Ya has votado en esta ronda.'], 400); // 400 Bad Request
            }

            // Devolver cualquier otro error de base de datos
            return response()->json(['message' => 'Error en la base de datos: ' . $e->getMessage()], 500);
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
