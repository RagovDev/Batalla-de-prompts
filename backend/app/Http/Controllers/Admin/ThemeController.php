<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ThemeController extends Controller
{
    /**
     * Muestra todos los temas.
     * Display a listing of the resource.
     */
    public function index()
    {
        // Devuelve todos los temas, ordenados por ronda y luego por ID
        return Theme::orderBy('ronda')->orderBy('id')->get();
    }

    /**
     * Crea un nuevo tema.
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'ronda' => 'required|integer|min:1|max:4',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 'image' es el archivo
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            // Guarda la imagen de muestra en 'storage/app/public/themes'
            $path = $request->file('image')->store('themes', 'public');
        }

        $theme = Theme::create([
            'title' => $data['title'],
            'ronda' => $data['ronda'],
            'image_url' => $path, // Guarda la ruta
            'votes' => 0,
        ]);

        return response()->json($theme, 201); // 201 Created
    }

    /**
     * Muestra un tema específico.
     * Display the specified resource.
     */
    public function show(Theme $theme)
    {
        // Gracias al Route Model Binding, Laravel ya encontró el tema
        return $theme;
    }

    /**
     * Actualiza un tema existente.
     * Update the specified resource in storage.
     */
    public function update(Request $request, Theme $theme)
    {
        // 'sometimes' significa: si el campo está presente, debe ser requerido, 
        // pero si no está presente, no pasa nada.
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'ronda' => 'sometimes|required|integer|min:1|max:4',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Usamos array_filter para actualizar solo los campos que se enviaron
        $theme->fill(array_filter([
            'title' => $data['title'] ?? null,
            'ronda' => $data['ronda'] ?? null,
        ]));

        if ($request->hasFile('image')) {
            if ($theme->image_url) {
                Storage::disk('public')->delete($theme->image_url);
            }
            $path = $request->file('image')->store('themes', 'public');
            $theme->image_url = $path;
        }

        $theme->save();

        return response()->json($theme);
    }

    /**
     * Borra un tema.
     * Remove the specified resource from storage.
     */
    public function destroy(Theme $theme)
   {
        // 1. Borra el archivo de imagen si existe
        if ($theme->image_url) {
            Storage::disk('public')->delete($theme->image_url);
        }

        // 2. Borra el registro de la base de datos
        // Nota: Esto fallará si hay 'themeVotes' apuntando a este tema
        // (Debido a la restricción de clave foránea)
        // Deberíamos añadir ->onDelete('cascade') a la migración de 'theme_votes'
        try {
            $theme->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['message' => 'No se puede borrar el tema porque tiene votos asociados. Primero reinicie el concurso.'], 400);
        }

        return response()->json(['message' => 'Tema borrado con éxito.'], 200);
    }
}
