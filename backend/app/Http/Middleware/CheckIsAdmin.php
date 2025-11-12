<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // Importa Auth

class CheckIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       // 1. Verifica si el usuario está autenticado Y si es administrador
        if (Auth::check() && Auth::user()->is_admin) {
            // 2. Si lo es, deja pasar la petición
            return $next($request);
        }

        // 3. Si no, lo rechaza
        // Si es una petición de API, devuelve un error JSON
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Acción no autorizada. Solo para administradores.'], 403);
        }

        // Si es una petición web, redirige al login
        return redirect('/login');
    }
}
