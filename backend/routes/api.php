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
use App\Http\Controllers\Admin\ThemeController as AdminThemeController; // Le damos un alias

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

    // Esta línea crea automáticamente:
    // GET /api/admin/themes -> AdminThemeController@index
    // POST /api/admin/themes -> AdminThemeController@store
    // GET /api/admin/themes/{theme} -> AdminThemeController@show
    // PUT /api/admin/themes/{theme} -> AdminThemeController@update
    // DELETE /api/admin/themes/{theme} -> AdminThemeController@destroy
    Route::apiResource('themes', AdminThemeController::class);

    // Aquí podemos añadir otras rutas de admin en el futuro
    // Ej: Route::get('/stats', [AdminStatsController::class, 'index']);
});
});