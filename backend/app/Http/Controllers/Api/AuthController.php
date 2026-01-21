<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registro de nuevos usuarios
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // El 'mutator' en el modelo User se encarga del hash
        ]);

        return response()->json(['message' => 'Usuario registrado con éxito.'], 201);
    }

    /**
     * Login de usuarios
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Buscar al usuario por email
        $user = User::where('email', $request->email)->first();

        // Verificar usuario y contraseña
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')], // Mensaje de "Credenciales inválidas"
            ]);
        }

        // Revocar tokens antiguos y crear uno nuevo
        $user->tokens()->delete(); // Opcional: invalida tokens de sesiones antiguas
        $token = $user->createToken('api-token-for-'.$user->name)->plainTextToken;

        // Devolver el token
        return response()->json([
            'token' => $token
        ]);
    }
}
