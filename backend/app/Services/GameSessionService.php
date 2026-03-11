<?php

namespace App\Services;

use App\Models\GameSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GameSessionService
{
    protected TimerService $timerService;
    protected RankingService $rankingService;

    public function __construct(TimerService $timerService, RankingService $rankingService)
    {
        $this->timerService = $timerService;
        $this->rankingService = $rankingService;
    }

    /**
     * Create a new game session for a user.
     * Enforces single active session constraint.
     *
     * @param User $user
     * @return GameSession
     */
    public function createSession(User $user): GameSession
    {
        return DB::transaction(function () use ($user) {
            // Abandon any existing active sessions
            $this->abandonActiveSession($user);

            // Create new session
            $session = GameSession::create([
                'user_id' => $user->id,
                'started_at' => now(),
                'time_remaining' => $this->timerService->getInitialTimer(),
                'status' => 'active',
            ]);

            return $session;
        });
    }

    /**
     * Get the active session for a user.
     *
     * @param User $user
     * @return GameSession|null
     */
    public function getActiveSession(User $user): ?GameSession
    {
        return GameSession::where('user_id', $user->id)
            ->active()
            ->first();
    }

    /**
     * Sync the timer with the server.
     *
     * @param GameSession $session
     * @param int $clientTimeRemaining
     * @return GameSession
     */
    public function syncTimer(GameSession $session, int $clientTimeRemaining): GameSession
    {
        if (!$session->isActive()) {
            return $session;
        }

        // Calculate server-side time remaining
        $serverTimeRemaining = $this->timerService->calculateTimeRemaining($session);

        // Update session with server time
        $session->updateTimeRemaining($serverTimeRemaining);

        return $session->fresh();
    }

    /**
     * Complete a game session.
     *
     * @param GameSession $session
     * @return GameSession
     */
    public function completeSession(GameSession $session): GameSession
    {
        if (!$session->isActive()) {
            throw new \Exception('Cannot complete a non-active session');
        }

        if ($session->hasTimedOut()) {
            throw new \Exception('Cannot complete a timed out session');
        }

        $session->markAsCompleted();

        // Update ranking with completion time
        $this->rankingService->updateRanking(
            $session->user_id,
            $session->completion_time
        );

        return $session->fresh();
    }

    /**
     * Abandon a game session.
     *
     * @param GameSession $session
     * @return GameSession
     */
    public function abandonSession(GameSession $session): GameSession
    {
        if (!$session->isActive()) {
            throw new \Exception('Cannot abandon a non-active session');
        }

        $session->markAsAbandoned();

        return $session->fresh();
    }

    /**
     * Abandon any active session for a user.
     *
     * @param User $user
     * @return void
     */
    protected function abandonActiveSession(User $user): void
    {
        $activeSession = $this->getActiveSession($user);

        if ($activeSession) {
            $activeSession->markAsAbandoned();
        }
    }

    /**
     * Validate that a session can accept actions.
     *
     * @param GameSession $session
     * @return bool
     */
    public function canAcceptActions(GameSession $session): bool
    {
        return $session->isActive() && !$session->hasTimedOut();
    }

    /**
     * Get session state with current time remaining.
     *
     * @param GameSession $session
     * @return array
     */
    public function getSessionState(GameSession $session): array
    {
        $timeRemaining = $session->isActive() 
            ? $this->timerService->calculateTimeRemaining($session)
            : $session->time_remaining;

        return [
            'id' => $session->id,
            'user_id' => $session->user_id,
            'started_at' => $session->started_at->toISOString(),
            'time_remaining' => $timeRemaining,
            'status' => $session->status,
            'completion_time' => $session->completion_time,
            'completed_at' => $session->completed_at?->toISOString(),
        ];
    }
}
