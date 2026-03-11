<?php

namespace App\Http\Controllers;

use App\Services\GameSessionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GameSessionController extends Controller
{
    protected GameSessionService $gameSessionService;

    public function __construct(GameSessionService $gameSessionService)
    {
        $this->gameSessionService = $gameSessionService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Start a new game session.
     * POST /api/game/start
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function start(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $session = $this->gameSessionService->createSession($user);
            $sessionState = $this->gameSessionService->getSessionState($session);

            return response()->json([
                'success' => true,
                'message' => 'Game session started successfully',
                'data' => $sessionState,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start game session',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    /**
     * Get current game session state.
     * GET /api/game/session
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSession(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $session = $this->gameSessionService->getActiveSession($user);

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active game session found',
                    'errors' => ['No active session'],
                ], 404);
            }

            $sessionState = $this->gameSessionService->getSessionState($session);

            return response()->json([
                'success' => true,
                'message' => 'Session retrieved successfully',
                'data' => $sessionState,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve session',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    /**
     * Sync timer with server.
     * POST /api/game/sync
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'time_remaining' => 'required|integer|min:0|max:1500',
        ]);

        try {
            $user = $request->user();
            $session = $this->gameSessionService->getActiveSession($user);

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active game session found',
                    'errors' => ['No active session'],
                ], 404);
            }

            $session = $this->gameSessionService->syncTimer($session, $validated['time_remaining']);
            $sessionState = $this->gameSessionService->getSessionState($session);

            return response()->json([
                'success' => true,
                'message' => 'Timer synced successfully',
                'data' => $sessionState,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync timer',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    /**
     * Complete the game session.
     * POST /api/game/complete
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function complete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'time_remaining' => 'required|integer|min:0|max:1500',
        ]);

        try {
            $user = $request->user();
            $session = $this->gameSessionService->getActiveSession($user);

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active game session found',
                    'errors' => ['No active session'],
                ], 404);
            }

            // Sync timer first
            $session = $this->gameSessionService->syncTimer($session, $validated['time_remaining']);

            // Check if session can still be completed
            if (!$this->gameSessionService->canAcceptActions($session)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot complete session - game over',
                    'errors' => ['Session has timed out or is not active'],
                ], 400);
            }

            $session = $this->gameSessionService->completeSession($session);
            $sessionState = $this->gameSessionService->getSessionState($session);

            return response()->json([
                'success' => true,
                'message' => 'Game completed successfully',
                'data' => $sessionState,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete game',
                'errors' => [$e->getMessage()],
            ], 400);
        }
    }

    /**
     * Abandon the current game session.
     * POST /api/game/abandon
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function abandon(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $session = $this->gameSessionService->getActiveSession($user);

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active game session found',
                    'errors' => ['No active session'],
                ], 404);
            }

            $session = $this->gameSessionService->abandonSession($session);
            $sessionState = $this->gameSessionService->getSessionState($session);

            return response()->json([
                'success' => true,
                'message' => 'Game session abandoned',
                'data' => $sessionState,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to abandon session',
                'errors' => [$e->getMessage()],
            ], 400);
        }
    }
}
