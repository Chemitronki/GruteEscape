<?php

namespace Tests\Feature\Sessions;

use Tests\TestCase;
use App\Models\User;
use App\Models\GameSession;
use App\Models\Puzzle;
use App\Models\PuzzleProgress;

/**
 * CRITICAL TEST SUITE: Puzzle Loading Issue
 * 
 * These are the MOST IMPORTANT tests. If these pass, the puzzle loading issue is FIXED.
 * If these fail, users cannot play the game.
 */
class PuzzleLoadingCriticalTest extends TestCase
{
    /**
     * CRITICAL TEST #1: Session creates first puzzle progress
     * 
     * This is THE most important test. If this fails, puzzles won't load.
     */
    public function test_session_creation_creates_first_puzzle_progress(): void
    {
        $user = User::factory()->create();
        
        // Create session
        $response = $this->actingAs($user)->postJson('/api/game/start');
        $this->assertEquals(201, $response->status());
        
        $sessionId = $response->json('session.id');
        $this->assertNotNull($sessionId);
        
        // Verify first puzzle progress exists
        $firstPuzzle = Puzzle::where('sequence_order', 1)->first();
        $this->assertNotNull($firstPuzzle, 'First puzzle missing from database');
        
        $progress = PuzzleProgress::where('game_session_id', $sessionId)
            ->where('puzzle_id', $firstPuzzle->id)
            ->first();
        
        $this->assertNotNull($progress, 'CRITICAL: First puzzle progress not created!');
    }

    /**
     * CRITICAL TEST #2: API returns puzzle data correctly
     * 
     * If this fails, the frontend cannot display the puzzle.
     */
    public function test_api_returns_puzzle_with_all_required_fields(): void
    {
        $user = User::factory()->create();
        
        // Create session
        $sessionResponse = $this->actingAs($user)->postJson('/api/game/start');
        $sessionId = $sessionResponse->json('session.id');
        
        // Get puzzle
        $response = $this->actingAs($user)->getJson("/api/puzzles/{$sessionId}/current");
        $this->assertEquals(200, $response->status(), 'Failed to get puzzle');
        
        $puzzle = $response->json('puzzle');
        $this->assertNotNull($puzzle, 'Puzzle is null');
        $this->assertNotNull($puzzle['id'], 'Puzzle ID is null');
        $this->assertNotNull($puzzle['title'], 'Puzzle title is null');
        $this->assertNotNull($puzzle['description'], 'Puzzle description is null');
        $this->assertNotNull($puzzle['type'], 'Puzzle type is null');
        $this->assertNotNull($puzzle['solution_data'], 'Puzzle solution_data is null');
    }

    /**
     * CRITICAL TEST #3: Solution data is properly formatted
     * 
     * If this fails, the frontend cannot parse the puzzle data.
     */
    public function test_solution_data_is_valid_json(): void
    {
        $user = User::factory()->create();
        
        $sessionResponse = $this->actingAs($user)->postJson('/api/game/start');
        $sessionId = $sessionResponse->json('session.id');
        
        $response = $this->actingAs($user)->getJson("/api/puzzles/{$sessionId}/current");
        $puzzle = $response->json('puzzle');
        
        $solutionData = $puzzle['solution_data'];
        $this->assertIsArray($solutionData, 'solution_data is not an array');
        $this->assertArrayHasKey('solution', $solutionData, 'solution_data missing solution key');
        $this->assertNotEmpty($solutionData['solution'], 'solution is empty');
    }

    /**
     * CRITICAL TEST #4: Complete flow works
     * 
     * Login → Start Game → Get Puzzle
     */
    public function test_complete_login_to_puzzle_flow(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);
        
        // Login
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => $password,
        ]);
        $this->assertEquals(200, $loginResponse->status());
        $token = $loginResponse->json('token');
        
        // Start game
        $sessionResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/game/start');
        $this->assertEquals(201, $sessionResponse->status());
        $sessionId = $sessionResponse->json('session.id');
        
        // Get puzzle
        $puzzleResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/puzzles/{$sessionId}/current");
        $this->assertEquals(200, $puzzleResponse->status());
        
        $puzzle = $puzzleResponse->json('puzzle');
        $this->assertNotNull($puzzle['id']);
        $this->assertNotNull($puzzle['title']);
        $this->assertNotNull($puzzle['solution_data']);
    }

    /**
     * CRITICAL TEST #5: Puzzle progression works
     * 
     * Solving puzzle 1 should load puzzle 2
     */
    public function test_puzzle_progression_works(): void
    {
        $user = User::factory()->create();
        
        $sessionResponse = $this->actingAs($user)->postJson('/api/game/start');
        $sessionId = $sessionResponse->json('session.id');
        
        // Get first puzzle
        $puzzle1Response = $this->actingAs($user)->getJson("/api/puzzles/{$sessionId}/current");
        $puzzle1 = $puzzle1Response->json('puzzle');
        $this->assertEquals(1, $puzzle1['sequence_order']);
        
        // Submit correct solution
        $submitResponse = $this->actingAs($user)->postJson("/api/puzzles/{$puzzle1['id']}/submit", [
            'session_id' => $sessionId,
            'solution' => $puzzle1['solution_data']['solution'],
        ]);
        
        if ($submitResponse->status() !== 200) {
            $this->fail("Submit failed with status {$submitResponse->status()}: " . json_encode($submitResponse->json()));
        }
        
        // Get second puzzle
        $puzzle2Response = $this->actingAs($user)->getJson("/api/puzzles/{$sessionId}/current");
        $puzzle2 = $puzzle2Response->json('puzzle');
        $this->assertEquals(2, $puzzle2['sequence_order']);
        $this->assertNotEquals($puzzle1['id'], $puzzle2['id']);
    }
}
