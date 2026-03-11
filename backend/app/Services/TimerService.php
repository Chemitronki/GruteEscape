<?php

namespace App\Services;

use App\Models\GameSession;
use Carbon\Carbon;

class TimerService
{
    /**
     * Initial timer value in seconds (25 minutes).
     */
    public const INITIAL_TIMER_SECONDS = 1500;

    /**
     * Calculate the current time remaining for a game session.
     *
     * @param GameSession $session
     * @return int Time remaining in seconds
     */
    public function calculateTimeRemaining(GameSession $session): int
    {
        if (!$session->isActive()) {
            return $session->time_remaining;
        }

        $elapsedSeconds = Carbon::parse($session->started_at)->diffInSeconds(now());
        $timeRemaining = self::INITIAL_TIMER_SECONDS - $elapsedSeconds;

        return max(0, $timeRemaining);
    }

    /**
     * Validate that the provided time remaining is reasonable.
     *
     * @param GameSession $session
     * @param int $clientTimeRemaining
     * @return bool
     */
    public function validateTimeRemaining(GameSession $session, int $clientTimeRemaining): bool
    {
        $serverTimeRemaining = $this->calculateTimeRemaining($session);
        
        // Allow 5 seconds tolerance for network latency
        $tolerance = 5;
        
        return abs($serverTimeRemaining - $clientTimeRemaining) <= $tolerance;
    }

    /**
     * Get the initial timer value.
     *
     * @return int
     */
    public function getInitialTimer(): int
    {
        return self::INITIAL_TIMER_SECONDS;
    }

    /**
     * Calculate completion time (time taken to complete).
     *
     * @param GameSession $session
     * @return int Time taken in seconds
     */
    public function calculateCompletionTime(GameSession $session): int
    {
        return self::INITIAL_TIMER_SECONDS - $session->time_remaining;
    }
}
