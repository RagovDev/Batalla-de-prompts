<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\User;
use App\Models\Vote; // Necesario para replicar la salida antigua
use Illuminate\Http\Request;
use Illuminate\Support\Collection; // Para manejar colecciones/arrays

class DashboardController extends Controller
{
    /**
     * Calcula y devuelve el dashboard de puntuaciones.
     * Display a listing of the resource.
     */
    public function index()
    {
        $rounds = [1, 2, 3, 4];
        $participants = new Collection(); // Usaremos una Colección de Laravel

        // 1. Obtener todos los nombres de usuarios que han subido al menos una imagen
        $participantNames = Image::with('user:id,name')
                                ->get()
                                ->pluck('user.name')
                                ->unique();

        // 2. Inicializar la estructura de datos
        foreach ($participantNames as $name) {
            $participants[$name] = [
                'name' => $name,
                'puntos' => [0, 0, 0, 0],
                'total' => 0,
                'roundData' => [
                    1 => ['votes' => 0, 'points' => 0, 'finalScore' => 0],
                    2 => ['votes' => 0, 'points' => 0, 'finalScore' => 0],
                    3 => ['votes' => 0, 'points' => 0, 'finalScore' => 0],
                    4 => ['votes' => 0, 'points' => 0, 'finalScore' => 0],
                ]
            ];
        }

        // 3. Iterar por cada ronda
        foreach ($rounds as $r) {
            $weightedImages = Image::where('ronda', $r)
                                  ->with('user:id,name')
                                  ->get()
                                  ->map(function ($image) use ($r) {
                                      $image->weightedVotes = $image->votes * $r;
                                      return $image;
                                  })
                                  ->sortByDesc('weightedVotes');

            // 5. Asignar 4 puntos por defecto
            foreach ($weightedImages as $img) {
                $name = $img->user->name;
                if (isset($participants[$name])) {
                    // --- CORRECCIÓN DE MODIFICACIÓN INDIRECTA ---
                    // 1. Sacar el array
                    $participantData = $participants[$name];

                    // 2. Modificar el array temporal
                    $classificationPoints = 4;
                    $finalRoundScore = (0 * $r) + $classificationPoints; // 4
                    $participantData['puntos'][$r - 1] = $classificationPoints;
                    $participantData['roundData'][$r] = [
                        'votes' => 0,
                        'points' => $classificationPoints,
                        'finalScore' => $finalRoundScore
                    ];
                    
                    // 3. Volver a meter el array modificado
                    $participants[$name] = $participantData;
                }
            }

            // 6. Sobrescribir los 3 primeros lugares
            $top3 = $weightedImages->values()->take(3);
            
            foreach ($top3 as $idx => $img) {
                $name = $img->user->name;
                if (isset($participants[$name])) {
                    // --- CORRECCIÓN DE MODIFICACIÓN INDIRECTA ---
                    // 1. Sacar el array
                    $participantData = $participants[$name];

                    // 2. Modificar el array temporal
                    $rank = $idx + 1;
                    $classificationPoints = 0;
                    if ($rank === 1) $classificationPoints = 10;
                    else if ($rank === 2) $classificationPoints = 8;
                    else if ($rank === 3) $classificationPoints = 6;
                    
                    $finalRoundScore = ($img->votes * $r) + $classificationPoints;

                    $participantData['puntos'][$r - 1] = $classificationPoints;
                    $participantData['roundData'][$r] = [
                        'votes' => $img->votes,
                        'points' => $classificationPoints,
                        'finalScore' => $finalRoundScore
                    ];
                    
                    // 3. Volver a meter el array modificado
                    $participants[$name] = $participantData;
                }
            }
        } // Fin del loop de rondas

        // 7. Calcular el total final
        foreach ($participants as $name => $data) {
            // --- CORRECCIÓN DE MODIFICACIÓN INDIRECTA ---
            // 1. Sacar el array
            $participantData = $participants[$name]; // $data es una copia

            // 2. Modificar el array temporal
            $totalScore = $participantData['roundData'][1]['finalScore'] +
                          $participantData['roundData'][2]['finalScore'] +
                          $participantData['roundData'][3]['finalScore'] +
                          $participantData['roundData'][4]['finalScore'];
                          
            $participantData['total'] = $totalScore;

            // 3. Volver a meter el array modificado
            $participants[$name] = $participantData;
        }
        
        // 8. Ordenar y devolver
        $sortedParticipants = $participants->values()->sortByDesc('total')->values();

        // 9. Devolver la respuesta
        return response()->json([
            'images' => Image::all(), 
            'votes' => Vote::all(), 
            'scores' => $sortedParticipants
        ]);
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
