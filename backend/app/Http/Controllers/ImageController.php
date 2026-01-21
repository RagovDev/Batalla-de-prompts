<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ImageController extends Controller
{
    /**
     * Muestra las imágenes de una ronda específica.
     * Display a listing of the resource.
     */
    public function index($ronda)
    {
        // 1. Validar que la ronda sea un número (buena práctica)
        if (!is_numeric($ronda) || $ronda < 1 || $ronda > 4) {
             return response()->json(['message' => 'Ronda inválida'], 400);
        }

        // 2. Consultar la base de datos con Eloquent
        $images = Image::where('ronda', $ronda) // Busca imágenes con esa ronda
                       ->with('user:id,name')  // Carga la relación 'user'
                       ->orderBy('created_at', 'asc') // Ordena por fecha de subida
                       ->get();

        // 3. Formatear la respuesta para el frontend
        $formattedImages = $images->map(function ($image) {
            return [
                'id' => $image->id,
                'name' => $image->user ? $image->user->name : 'Usuario Desconocido', // Obtenemos el nombre del usuario
                'url' => Storage::url($image->image_url), // Genera la URL pública
                'ronda' => $image->ronda,
                'votes' => $image->votes
            ];
        });

        // 4. Devolver la respuesta JSON
        return response()->json($formattedImages);
    }

    /**
     * Sube una nueva imagen para una ronda específica.
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $ronda)
    {
        // 1. Validar la petición
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Reglas de validación para la imagen
        ]);

        // 2. Obtener el usuario autenticado (desde el token de Sanctum)
        $user = Auth::user(); // Laravel lo hace automáticamente si estás autenticado
        
        // 3. Validar si el usuario ya subió una imagen para esta ronda
        $existingImage = Image::where('user_id', $user->id)
                              ->where('ronda', $ronda)
                              ->first();

        if ($existingImage) {
            return response()->json(['message' => 'Ya has subido una imagen para esta ronda.'], 400);
        }

        // 4. Guardar el archivo físico
        // La carpeta 'uploads' estará dentro de 'storage/app/public/'
        // Necesitas `php artisan storage:link` para que sea accesible desde /storage/uploads/
        $filename = $request->file('image')->store('uploads', 'public');

        // 5. Guardar la información de la imagen en la base de datos
        $image = Image::create([
            'user_id' => $user->id,
            'image_url' => $filename,
            'ronda' => $ronda,
            'votes' => 0, // Las imágenes nuevas empiezan con 0 votos
        ]);

        // 6. Preparar y devolver la respuesta JSON
        return response()->json([
            'id' => $image->id,
            'name' => $user->name, // Nombre del usuario que subió
            'url' => Storage::url($image->filename), // URL pública
            'ronda' => $image->ronda,
            'votes' => $image->votes,
            'message' => 'Imagen subida exitosamente.'
        ], 201); // 201 Created
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
     * Borra una imagen específica.
     * Remove the specified resource from storage.
     */
    public function destroy(Image $image)
    {
        // 1. AUTORIZACIÓN
        // Llama a la 'ImagePolicy' que creamos.
        // Si el usuario no es el dueño, Laravel devolverá un 403 automáticamente.
        $this->authorize('delete', $image);

        // 2. BORRAR EL ARCHIVO FÍSICO 
        // Usamos la columna 'image_url' que guarda la ruta (ej: 'uploads/archivo.jpg')
        Storage::disk('public')->delete($image->image_url);

        // 3. BORRAR DE LA BASE DE DATOS 
        // Al borrar la imagen, MySQL (gracias a 'onDelete('cascade')' 
        // en tu migración) borrará automáticamente todos los votos asociados.
        $image->delete();

        // 4. DEVOLVER RESPUESTA 
        return response()->json(['success' => true, 'message' => 'Imagen borrada correctamente.'], 200);
    }
}
