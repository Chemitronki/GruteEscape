<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    // These routes will be implemented in Task 2
    // Route::post('/register', [AuthController::class, 'register']);
    // Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // Game session routes
    // Route::prefix('game')->group(function () {
    //     Route::post('/start', [GameSessionController::class, 'start']);
    //     Route::get('/session', [GameSessionController::class, 'current']);
    //     Route::post('/sync', [GameSessionController::class, 'sync']);
    //     Route::post('/complete', [GameSessionController::class, 'complete']);
    //     Route::post('/abandon', [GameSessionController::class, 'abandon']);
    // });
    
    // Puzzle routes
    // Route::prefix('puzzles')->group(function () {
    //     Route::get('/{sessionId}', [PuzzleController::class, 'index']);
    //     Route::post('/{puzzleId}/submit', [PuzzleController::class, 'submit']);
    //     Route::get('/{puzzleId}/progress', [PuzzleController::class, 'progress']);
    //     Route::get('/{puzzleId}/hints/available', [HintController::class, 'available']);
    //     Route::get('/{puzzleId}/hints/{level}', [HintController::class, 'show']);
    // });
    
    // Ranking routes
    // Route::prefix('ranking')->group(function () {
    //     Route::get('/top', [RankingController::class, 'top']);
    //     Route::get('/user/{userId}', [RankingController::class, 'userRank']);
    // });
});
