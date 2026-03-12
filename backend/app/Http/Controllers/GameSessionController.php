<?php

namespace App\Http\Controllers;

use App\Events\GameCompleted;
use App\Models\GameSession;
use App\Models\Puzzle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameSessionController extends Controller
{
    public function start(Request $request)
    {
        $user = Auth::user();
        
        // Check if user already has an active session
        $activeSession = GameSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
        
        if ($activeSession) {
            return response()->json([
                'message' => 'El usuario ya tiene una sesión activa',
                'session' => $activeSession
            ]);
        }
        
        $session = GameSession::create([
            'user_id' => $user->id,
            'started_at' => now(),
            'time_remaining' => 1500,
            'status' => 'active'
        ]);
        
        return response()->json([
            'message' => 'Sesión de juego iniciada',
            'session' => $session
        ], 201);
    }
    
    public function getSession(Request $request)
    {
        $user = Auth::user();
        
        $session = GameSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
        
        if (!$session) {
            return response()->json([
                'message' => 'No se encontró sesión activa'
            ], 404);
        }
        
        // Calculate time remaining
        $elapsedSeconds = now()->diffInSeconds($session->started_at);
        $timeRemaining = max(0, 1500 - $elapsedSeconds);
        
        if ($timeRemaining <= 0) {
            $session->update([
                'status' => 'timeout',
                'completed_at' => now()
            ]);
            
            return response()->json([
                'message' => 'La sesión ha expirado',
                'session' => $session
            ]);
        }
        
        return response()->json([
            'session' => $session,
            'time_remaining' => $timeRemaining
        ]);
    }
    
    public function sync(Request $request)
    {
        $user = Auth::user();
        
        $session = GameSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
        
        if (!$session) {
            return response()->json([
                'message' => 'No se encontró sesión activa'
            ], 404);
        }
        
        $elapsedSeconds = now()->diffInSeconds($session->started_at);
        $timeRemaining = max(0, 1500 - $elapsedSeconds);
        
        if ($timeRemaining <= 0) {
            $session->update([
                'status' => 'timeout',
                'completed_at' => now()
            ]);
            
            return response()->json([
                'message' => 'La sesión ha expirado',
                'session' => $session
            ]);
        }
        
        return response()->json([
            'time_remaining' => $timeRemaining
        ]);
    }
    
    public function complete(Request $request)
    {
        $user = Auth::user();
        
        $session = GameSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
        
        if (!$session) {
            return response()->json([
                'message' => 'No se encontró sesión activa'
            ], 404);
        }
        
        $completionTime = now()->diffInSeconds($session->started_at);
        
        $session->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_time' => $completionTime
        ]);
        
        // Dispatch event to update ranking
        GameCompleted::dispatch($session);
        
        return response()->json([
            'message' => 'Juego completado',
            'session' => $session,
            'completion_time' => $completionTime
        ]);
    }
    
    public function abandon(Request $request)
    {
        $user = Auth::user();
        
        $session = GameSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
        
        if (!$session) {
            return response()->json([
                'message' => 'No se encontró sesión activa'
            ], 404);
        }
        
        $session->update([
            'status' => 'abandoned',
            'completed_at' => now()
        ]);
        
        return response()->json([
            'message' => 'Juego abandonado',
            'session' => $session
        ]);
    }
}
