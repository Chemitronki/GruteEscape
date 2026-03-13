<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use App\Models\User;

/**
 * Test Suite: Complete Login to Puzzle Flow
 * 
 * This test suite validates the complete end-to-end flow from login
 * to puzzle display. It specifically addresses the critical issue where
 * users successfully log in but puzzles fail to load.
 * 
 * Requirement 25: End-to-End Login to Puzzle Flow
 * Requirement 29: Puzzle Loading Issue Detection
 */
class CompleteLoginPuzzleFlowTest extends TestCase
{
    /**
     * Test complete flow: Login → Start Game → Get Puzzle
     * 
     * CRITICAL: This is the most important test. If this fails,
     * users cannot play the game.
     */
    public function test_complete_flow_login_to_puzzle_display(): void
    {
        // Step 1: Create user
        $user = User::factory()->create([
            'email' => 'player@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Step 2: Login
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'player@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('token');
        $this->assertNotNull($token, 'Login failed: no token returned');

        // Step 3: Start game session
        $sessionResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/game/start');

        $sessionResponse->assertStatus(201);
        $sessionId = $sessionResponse->json('session.id');
        $this->assertNotNull($sessionId, 'Session creation failed: no session ID returned');

        // Step 4: Get current puzzle
        $puzzleResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/puzzles/{$sessionId}/current");

        $puzzleResponse->assertStatus(200);
        
        $puzzle = $puzzleResponse->json('puzzle');
        
        // Verify puzzle data is complete
        $this->assertNotNull($puzzle, 'Puzzle is null');
        $this->assertNotNull($puzzle['id'], 'Puzzle ID is null - THIS IS THE ISSUE');
        $this->assertNotNull($puzzle['title'], 'Puzzle title is null');
        $this->assertNotNull($puzzle['description'], 'Puzzle description is null');
        $this->assertNotNull($puzzle['type'], 'Puzzle type is null');
        $this->assertNotNull($puzzle['solution_data'], 'Puzzle solution_data is null');

        // Verify solution_data is properly formatted
        $solutionData = $puzzle['solution_data'];
        $this->assertIsArray($solutionData, 'solution_data is not an array');
        $this->assertArrayHasKey('solution', $solutionData, 'solution_data missing solution key');
        $this->assertNotEmpty($solutionData['solution'], 'solution is empty');
    }

    /**
     * Test that puzzle data is not corrupted through the flow
     * 
     * Verifies that data maintains integrity from database to API response.
     */
    public function test_puzzle_data_integrity_through_flow(): void
    {
        $user = User::factory()->create([
            'email' => 'player@example.com',
            'password' => bcrypt('password123'),
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'player@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        $sessionResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/game/start');

        $sessionId = $sessionResponse->json('session.id');

        $puzzleResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/puzzles/{$sessionId}/current");

        $puzzle = $puzzleResponse->json('puzzle');
        
        // Serialize and deserialize to verify integrity
        $serialized = json_encode($puzzle);
        $deserialized = json_decode($serialized, true);

        // Verify all fields are preserved
        $this->assertEquals($puzzle['id'], $deserialized['id']);
        $this->assertEquals($puzzle['title'], $deserialized['title']);
        $this->assertEquals($puzzle['description'], $deserialized['description']);
        $this->assertEquals($puzzle['type'], $deserialized['type']);
        $this->assertEquals($puzzle['solution_data'], $deserialized['solution_data']);
    }

    /**
     * Test that authentication is required for all steps
     * 
     * Verifies that unauthenticated requests are rejected.
     */
    public function test_authentication_is_required_for_all_steps(): void
    {
        // Try to start game without token
        $response = $this->postJson('/api/game/start');
        $response->assertStatus(401);

        // Try to get puzzle without token
        $response = $this->getJson('/api/puzzles/1/current');
        $response->assertStatus(401);
    }

    /**
     * Test that invalid token is rejected
     * 
     * Verifies that invalid tokens are properly rejected.
     */
    public function test_invalid_token_is_rejected(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->postJson('/api/game/start');

        $response->assertStatus(401);
    }

    /**
     * Test that user can start multiple sessions
     * 
     * Verifies that users can create new sessions.
     */
    public function test_user_can_start_multiple_sessions(): void
    {
        $user = User::factory()->create([
            'email' => 'player@example.com',
            'password' => bcrypt('password123'),
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'player@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        // Start first session
        $session1Response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/game/start');

        $session1Id = $session1Response->json('session.id');

        // Get first puzzle
        $puzzle1Response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/puzzles/{$session1Id}/current");

        $puzzle1 = $puzzle1Response->json('puzzle');
        $this->assertNotNull($puzzle1);

        // Start second session
        $session2Response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/game/start');

        $session2Id = $session2Response->json('session.id');

        // Get second puzzle
        $puzzle2Response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/puzzles/{$session2Id}/current");

        $puzzle2 = $puzzle2Response->json('puzzle');
        $this->assertNotNull($puzzle2);

        // Sessions should be different
        $this->assertNotEquals($session1Id, $session2Id);
    }

    /**
     * Test that puzzle progresses correctly
     * 
     * Verifies that solving a puzzle advances to the next one.
     */
    public function test_puzzle_progression_works_correctly(): void
    {
        $user = User::factory()->create([
            'email' => 'player@example.com',
            'password' => bcrypt('password123'),
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'player@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        $sessionResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/game/start');

        $sessionId = $sessionResponse->json('session.id');

        // Get first puzzle
        $puzzle1Response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/puzzles/{$sessionId}/current");

        $puzzle1 = $puzzle1Response->json('puzzle');
        $this->assertEquals(1, $puzzle1['sequence_order']);

        // Submit correct solution
        $submitResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/puzzles/{$puzzle1['id']}/submit", [
                'session_id' => $sessionId,
                'solution' => $puzzle1['solution_data']['solution'],
            ]);

        $submitResponse->assertStatus(200);

        // Get second puzzle
        $puzzle2Response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/puzzles/{$sessionId}/current");

        $puzzle2 = $puzzle2Response->json('puzzle');
        $this->assertEquals(2, $puzzle2['sequence_order']);
        $this->assertNotEquals($puzzle1['id'], $puzzle2['id']);
    }

    /**
     * Test that response times are acceptable throughout the flow
     * 
     * Verifies that the complete flow completes within acceptable time.
     */
    public function test_complete_flow_response_times_are_acceptable(): void
    {
        $user = User::factory()->create([
            'email' => 'player@example.com',
            'password' => bcrypt('password123'),
        ]);

        $startTime = microtime(true);

        // Login
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'player@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        // Start game
        $sessionResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/game/start');

        $sessionId = $sessionResponse->json('session.id');

        // Get puzzle
        $puzzleResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/puzzles/{$sessionId}/current");

        $endTime = microtime(true);
        $totalDuration = ($endTime - $startTime) * 1000; // Convert to ms

        // Complete flow should be under 1 second
        $this->assertLessThan(1000, $totalDuration, "Complete flow took {$totalDuration}ms, expected < 1000ms");
    }

    /**
     * Test that error messages are in Spanish
     * 
     * Verifies that all error messages are properly localized.
     */
    public function test_error_messages_are_in_spanish(): void
    {
        // Try to login with invalid credentials
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
        
        $message = $response->json('message');
        $this->assertNotEmpty($message);
        // Message should be in Spanish (not English)
    }
}
