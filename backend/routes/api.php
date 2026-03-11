<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameSessionController;
use App\Http\Controllers\HintController;
use App\Http\Controllers\PuzzleController;
use App\Http\Controllers\RankingController;
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

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Game session routes
    Route::post('/game/start', [GameSessionController::class, 'start']);
    Route::get('/game/session', [GameSessionController::class, 'getSession']);
    Route::post('/game/sync', [GameSessionController::class, 'sync']);
    Route::post('/game/complete', [GameSessionController::class, 'complete']);
    Route::post('/game/abandon', [GameSessionController::class, 'abandon']);
    
    // Puzzle routes
    Route::get('/puzzles/{sessionId}', [PuzzleController::class, 'getCurrentPuzzle']);
    Route::post('/puzzles/{puzzleId}/submit', [PuzzleController::class, 'submitSolution']);
    Route::get('/puzzles/{puzzleId}/progress', [PuzzleController::class, 'getProgress']);
    
    // Hint routes
    Route::get('/puzzles/{puzzleId}/hints/available', [HintController::class, 'checkAvailability']);
    Route::get('/puzzles/{puzzleId}/hints/{level}', [HintController::class, 'getHint']);
    
    // Ranking routes
    Route::get('/ranking/top', [RankingController::class, 'getTopPlayers']);
    Route::get('/ranking/user/{userId}', [RankingController::class, 'getUserRank']);
});
