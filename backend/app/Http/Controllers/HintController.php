<?php

namespace App\Http\Controllers;

use App\Models\PuzzleProgress;
use App\Services\HintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HintController extends Controller
{
    protected HintService $hintService;

    public function __construct(HintService $hintService)
    {
        $this->hintService = $hintService;
    }

    /**
     * Check hint availability for a puzzle.
     *
     * @param int $puzzleId
     * @param Request $request
     * @return JsonResponse
     */
    public function checkAvailability(int $puzzleId, Request $request): JsonResponse
    {
        try {
            // Get the current user's active game session
            $user = $request->user();
            
            // Find the puzzle progress for this puzzle in the user's active session
            $progress = PuzzleProgress::whereHas('gameSession', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', 'active');
            })
            ->where('puzzle_id', $puzzleId)
            ->first();

            if (!$progress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Puzzle progress not found',
                    'errors' => ['No active puzzle progress found for this puzzle']
                ], 404);
            }

            $availability = $this->hintService->checkHintAvailability($progress->id);

            return response()->json([
                'success' => true,
                'data' => $availability,
                'message' => 'Hint availability checked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check hint availability',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Get a specific hint for a puzzle.
     *
     * @param int $puzzleId
     * @param int $level
     * @param Request $request
     * @return JsonResponse
     */
    public function getHint(int $puzzleId, int $level, Request $request): JsonResponse
    {
        try {
            // Validate level
            if ($level < 1 || $level > 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid hint level',
                    'errors' => ['Hint level must be between 1 and 3']
                ], 400);
            }

            // Get the current user's active game session
            $user = $request->user();
            
            // Find the puzzle progress for this puzzle in the user's active session
            $progress = PuzzleProgress::whereHas('gameSession', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('status', 'active');
            })
            ->where('puzzle_id', $puzzleId)
            ->first();

            if (!$progress) {
                return response()->json([
                    'success' => false,
                    'message' => 'Puzzle progress not found',
                    'errors' => ['No active puzzle progress found for this puzzle']
                ], 404);
            }

            $hint = $this->hintService->getHint($progress->id, $level);

            if (!$hint) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hint not available',
                    'errors' => ['This hint is not available yet or does not exist']
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $hint,
                'message' => 'Hint retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve hint',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }
}
