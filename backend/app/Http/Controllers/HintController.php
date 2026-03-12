<?php

namespace App\Http\Controllers;

use App\Models\Hint;
use App\Models\PuzzleProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HintController extends Controller
{
    public function checkAvailability(Request $request, $puzzleId)
    {
        $user = Auth::user();
        $sessionId = $request->input('session_id');
        
        $progress = PuzzleProgress::where('game_session_id', $sessionId)
            ->where('puzzle_id', $puzzleId)
            ->first();
        
        if (!$progress) {
            return response()->json([
                'message' => 'Progreso no encontrado'
            ], 404);
        }
        
        // Check if 120 seconds have passed
        $timeSpent = now()->diffInSeconds($progress->started_at);
        $canUseHint = $timeSpent >= 120 && $progress->hints_used < 3;
        
        return response()->json([
            'can_use_hint' => $canUseHint,
            'hints_used' => $progress->hints_used,
            'time_spent' => $timeSpent
        ]);
    }
    
    public function getHint(Request $request, $puzzleId, $level)
    {
        $user = Auth::user();
        $sessionId = $request->input('session_id');
        
        $progress = PuzzleProgress::where('game_session_id', $sessionId)
            ->where('puzzle_id', $puzzleId)
            ->first();
        
        if (!$progress) {
            return response()->json([
                'message' => 'Progreso no encontrado'
            ], 404);
        }
        
        // Check if 120 seconds have passed
        $timeSpent = now()->diffInSeconds($progress->started_at);
        if ($timeSpent < 120) {
            return response()->json([
                'message' => 'Las pistas aún no están disponibles'
            ], 403);
        }
        
        // Check if max hints reached
        if ($progress->hints_used >= 3) {
            return response()->json([
                'message' => 'Se ha alcanzado el máximo de pistas'
            ], 403);
        }
        
        $hint = Hint::where('puzzle_id', $puzzleId)
            ->where('level', $level)
            ->first();
        
        if (!$hint) {
            return response()->json([
                'message' => 'Pista no encontrada'
            ], 404);
        }
        
        // Increment hints used
        $progress->increment('hints_used');
        
        return response()->json([
            'hint' => $hint,
            'hints_used' => $progress->hints_used
        ]);
    }
}
