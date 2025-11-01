<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;      
use App\Http\Controllers\VoteController;        
use App\Http\Controllers\ThemeController;      
use App\Http\Controllers\DashboardController;   
use App\Http\Controllers\StatsController;  
use App\Http\Controllers\Api\AuthController;    

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

});