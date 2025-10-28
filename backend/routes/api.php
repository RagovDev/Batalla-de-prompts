<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;      
use App\Http\Controllers\VoteController;        
use App\Http\Controllers\ThemeController;      
use App\Http\Controllers\DashboardController;   
use App\Http\Controllers\StatsController;      

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes for authentication (handled by Breeze/Sanctum)
// Route::post('/register', ...); // Assuming Breeze handles this
// Route::post('/login', ...);    // Assuming Breeze handles this
// Route::post('/logout', ...);   // Assuming Breeze handles this

// Routes that don't necessarily require authentication
Route::get('/rondas/{ronda}/images', [ImageController::class, 'index']);
Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/stats', [StatsController::class, 'index']);
Route::get('/stats/temas/{ronda}', [StatsController::class, 'themeVotesByRound']);
Route::get('/stats/images/{ronda}', [StatsController::class, 'imageVotesByRound']);

// Routes protected by authentication (require a valid Sanctum token)
Route::middleware(['auth:sanctum'])->group(function () {
    // Get authenticated user info (default Sanctum route)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Image Upload & Deletion
    Route::post('/rondas/{ronda}/upload', [ImageController::class, 'upload']);
    Route::delete('/images/{image}', [ImageController::class, 'destroy']); 

    // Image Voting
    Route::post('/vote', [VoteController::class, 'store']);
    Route::get('/me/votes', [VoteController::class, 'userVotes']); 

    // Theme Listing & Voting
    Route::get('/temas/{ronda}', [ThemeController::class, 'index']); 
    Route::post('/temas/votar', [ThemeController::class, 'vote']);

});

