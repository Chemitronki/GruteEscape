<?php

namespace App\Services;

use App\Models\Hint;
use App\Models\PuzzleProgress;
use Carbon\Carbon;

class HintService
{
    /**
     * Check if hints are available for a puzzle based on time spent.
     *
     * @param int $puzzleProgressId
     * @return array
     */
    public function checkHintAvailability(int $puzzleProgressId): array
    {
        $progress = PuzzleProgress::findOrFail($puzzleProgressId);
        
        // Calculate time spent
        $timeSpent = $this->calculateTimeSpent($progress);
        
        // Check if 120 seconds (2 minutes) have passed
        $hintsAvailable = $timeSpent >= 120 && !$progress->is_completed;
        
        // Get total hints for this puzzle
        $totalHints = Hint::where('puzzle_id', $progress->puzzle_id)->count();
        
        // Calculate available hint level
        $nextHintLevel = min($progress->hints_used + 1, $totalHints);
        
        return [
            'available' => $hintsAvailable && $progress->hints_used < 3,
            'time_spent' => $timeSpent,
            'hints_used' => $progress->hints_used,
            'max_hints' => 3,
            'next_hint_level' => $nextHintLevel,
        ];
    }

    /**
     * Get a specific hint for a puzzle.
     *
     * @param int $puzzleProgressId
     * @param int $level
     * @return array|null
     */
    public function getHint(int $puzzleProgressId, int $level): ?array
    {
        $progress = PuzzleProgress::findOrFail($puzzleProgressId);
        
        // Validate hint availability
        $availability = $this->checkHintAvailability($puzzleProgressId);
        
        if (!$availability['available']) {
            return null;
        }
        
        // Check if requested level is valid
        if ($level > 3 || $level > $progress->hints_used + 1) {
            return null;
        }
        
        // Get the hint
        $hint = Hint::where('puzzle_id', $progress->puzzle_id)
            ->where('level', $level)
            ->first();
        
        if (!$hint) {
            return null;
        }
        
        // Update hints_used if this is a new hint
        if ($level > $progress->hints_used) {
            $progress->hints_used = $level;
            $progress->save();
        }
        
        return [
            'level' => $hint->level,
            'content' => $hint->content,
            'hints_used' => $progress->hints_used,
        ];
    }

    /**
     * Calculate time spent on a puzzle.
     *
     * @param PuzzleProgress $progress
     * @return int Time in seconds
     */
    private function calculateTimeSpent(PuzzleProgress $progress): int
    {
        if ($progress->is_completed) {
            return $progress->time_spent;
        }
        
        $startedAt = Carbon::parse($progress->started_at);
        $now = Carbon::now();
        
        return $now->diffInSeconds($startedAt);
    }
}
