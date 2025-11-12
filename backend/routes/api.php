<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;      
use App\Http\Controllers\VoteController;        
use App\Http\Controllers\ThemeController;      
use App\Http\Controllers\DashboardController;   
use App\Http\Controllers\StatsController;  
use App\Http\Controllers\Api\AuthController;    
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

// Routes that don't necessarily require authentication
Route::get('/rondas/{ronda}/images', [ImageController::class, 'index']);
Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/stats', [StatsController::class, 'index']);
Route::get('/stats/temas/{ronda}', [StatsController::class, 'themeVotesByRound']);
Route::get('/stats/images/{ronda}', [StatsController::class, 'imageVotesByRound']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protected by authentication (require a valid Sanctum token)
Route::middleware(['auth:sanctum'])->group(function () {
    // Get authenticated user info (default Sanctum route)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Image Upload & Deletion
    Route::post('/rondas/{ronda}/images', [ImageController::class, 'store']);
    Route::delete('/images/{image}', [ImageController::class, 'destroy']); 

    // Image Voting
    Route::post('/vote', [VoteController::class, 'store']);
    Route::get('/me/votes', [VoteController::class, 'userVotes']); 

    // Theme Listing & Voting
    Route::get('/temas/{ronda}', [ThemeController::class, 'index']); 
    Route::post('/temas/votar', [ThemeController::class, 'vote']);

    // ===================================================
    // RUTAS DEL PANEL DE ADMINISTRACIÓN
    // ===================================================
    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

        // Dashboard principal del admin
        // GET /api/admin/dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);

        // Aquí pondremos el resto de rutas de admin:
        // Ej: GET /api/admin/users (para ver lista de usuarios)
        // Ej: POST /api/admin/themes (para crear un nuevo tema)
        // Ej: PUT /api/admin/themes/{id} (para actualizar un tema)
        // Ej: DELETE /api/admin/themes/{id} (para borrar un tema)

    });
});