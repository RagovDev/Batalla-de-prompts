<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException; // Para capturar el error de voto duplicado

class VoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Almacena un nuevo voto en la base de datos.
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validar que nos envíen el ID de la imagen
        $request->validate([
            'imageId' => 'required|integer|exists:images,id'
        ]);

        // 2. Obtener la imagen y el usuario autenticado
        $image = Image::findOrFail($request->imageId);
        $user = Auth::user();

        // 3. Regla: Un usuario no puede votar por su propia imagen
        if ($image->user_id === $user->id) {
            return response()->json(['message' => 'No puedes votar por tu propia imagen.'], 403); // 403 Forbidden
        }

        try {
            // 4. Intentar crear el voto
            Vote::create([
                'user_id' => $user->id,
                'image_id' => $image->id,
                'ronda' => $image->ronda
            ]);

            // 5. Incrementar el contador de votos en la imagen
            $image->increment('votes'); // Esto suma 1 al contador 'votes' y guarda

            return response()->json([
                'success' => true, 
                'votes' => $image->votes
            ], 201); // 201 Created

        } catch (QueryException $e) {
            // 6. Manejar el error de voto duplicado
            // '23000' es el código SQL para violación de integridad (como una 'unique constraint')
            if ($e->getCode() == '23000') {
                return response()->json(['message' => 'Ya has votado en esta ronda.'], 400); // 400 Bad Request
            }

            // Devolver cualquier otro error de base de datos
            return response()->json(['message' => 'Error en la base de datos: ' . $e->getMessage()], 500);
        }
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

    /**
     * Devuelve el historial de votos (para imágenes) del usuario autenticado.
     */
    public function userVotes()
    {
        // 1. Obtener el ID del usuario autenticado
        $userId = Auth::id();

        // 2. Consultar la base de datos
        // Busca todos los votos que pertenecen a este usuario
        $userVotes = Vote::where('user_id', $userId)
                         ->get();

        // 3. Formatear la respuesta
        // Transforma la colección de votos en el formato { ronda: imageId }
        $formattedVotes = $userVotes->mapWithKeys(function ($vote) {
            return [$vote->ronda => $vote->image_id];
        });

        // 4. Devolver la respuesta JSON
        return response()->json($formattedVotes);
    }
}
