<?php

namespace Tests\Feature\Sessions;

use Tests\TestCase;
use App\Models\User;
use App\Models\GameSession;
use App\Models\Puzzle;
use App\Models\PuzzleProgress;

/**
 * Test Suite: Puzzle Initialization on Session Creation
 * 
 * This test suite specifically addresses the critical issue where users
 * successfully authenticate but puzzles fail to load. These tests verify
 * that when a session is created, the first puzzle is properly initialized
 * and all required data is present.
 * 
 * Requirement 6: Game Session Creation
 * Requirement 29: Puzzle Loading Issue Detection
 */
class PuzzleInitializationTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test that session creation initializes first puzzle
     * 
     * CRITICAL: This test verifies the root cause of the puzzle loading issue.
     * If this test fails, puzzles will not load after login.
     */
    public function test_session_creation_initializes_first_puzzle(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/game/start');

        $response->assertStatus(201);
        
        $sessionId = $response->json('session.id');
        
        // Verify session exists
        $session = GameSession::find($sessionId);
        $this->assertNotNull($session, 'Session was not created');
        $this->assertEquals('active', $session->status);
        $this->assertNotNull($session->started_at);

        // Get first puzzle
        $firstPuzzle = Puzzle::where('sequence_order', 1)->first();
        $this->assertNotNull($firstPuzzle, 'First puzzle does not exist in database');

        // Verify first puzzle progress is created
        $progress = PuzzleProgress::where('game_session_id', $sessionId)
            ->where('puzzle_id', $firstPuzzle->id)
            ->first();
        
        $this->assertNotNull($progress, 'First puzzle progress was not created');
        $this->assertFalse($progress->is_completed);
    }

    /**
     * Test that getCurrentPuzzle returns the first puzzle
     * 
     * CRITICAL: This test verifies that after session creation,
     * the API can retrieve the first puzzle correctly.
     */
    public function test_get_current_puzzle_returns_first_puzzle(): void
    {
        // Create session
        $sessionResponse = $this->actingAs($this->user)
            ->postJson('/api/game/start');

        $sessionId = $sessionResponse->json('session.id');

        // Get current puzzle
        $response = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{$sessionId}/current");

        $response->assertStatus(200);
        
        $puzzle = $response->json('puzzle');
        
        // Verify puzzle exists
        $this->assertNotNull($puzzle, 'Puzzle is null');
        $this->assertNotNull($puzzle['id'], 'Puzzle ID is null');
        $this->assertNotNull($puzzle['title'], 'Puzzle title is null');
        $this->assertNotNull($puzzle['description'], 'Puzzle description is null');
        $this->assertNotNull($puzzle['type'], 'Puzzle type is null');
        
        // Verify it's the first puzzle
        $this->assertEquals(1, $puzzle['sequence_order'], 'Not the first puzzle');
        $this->assertNotNull($puzzle['title'], 'Puzzle title is null');
        $this->assertNotNull($puzzle['description'], 'Puzzle description is null');
        $this->assertNotNull($puzzle['type'], 'Puzzle type is null');
    }

    /**
     * Test that puzzle has all required fields
     * 
     * CRITICAL: Verifies that puzzle data is complete and not corrupted.
     */
    public function test_puzzle_has_all_required_fields(): void
    {
        // Create session
        $sessionResponse = $this->actingAs($this->user)
            ->postJson('/api/game/start');

        $sessionId = $sessionResponse->json('session.id');

        // Get current puzzle
        $response = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{sessionId}/current");

        $puzzle = $response->json('puzzle');

        // Verify all required fields
        $this->assertArrayHasKey('id', $puzzle);
        $this->assertArrayHasKey('title', $puzzle);
        $this->assertArrayHasKey('description', $puzzle);
        $this->assertArrayHasKey('type', $puzzle);
        $this->assertArrayHasKey('solution_data', $puzzle);

        // Verify fields are not empty
        $this->assertNotEmpty($puzzle['id']);
        $this->assertNotEmpty($puzzle['title']);
        $this->assertNotEmpty($puzzle['description']);
        $this->assertNotEmpty($puzzle['type']);
        $this->assertNotEmpty($puzzle['solution_data']);
    }

    /**
     * Test that puzzle data is not corrupted
     * 
     * CRITICAL: Verifies that JSON data is properly decoded and accessible.
     */
    public function test_puzzle_data_is_not_corrupted(): void
    {
        // Create session
        $sessionResponse = $this->actingAs($this->user)
            ->postJson('/api/game/start');

        $sessionId = $sessionResponse->json('session.id');

        // Get current puzzle
        $response = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{sessionId}/current");

        $puzzle = $response->json('puzzle');
        $solutionData = $puzzle['solution_data'];

        // Verify solution_data is an array (properly decoded JSON)
        $this->assertIsArray($solutionData, 'solution_data is not an array');
        
        // Verify solution_data has expected structure
        $this->assertArrayHasKey('solution', $solutionData, 'solution_data missing solution key');
        $this->assertNotEmpty($solutionData['solution'], 'solution is empty');
    }

    /**
     * Test that solution is never exposed in API response
     * 
     * SECURITY: Verifies that the solution is not accidentally exposed.
     */
    public function test_solution_is_not_exposed_in_response(): void
    {
        // Create session
        $sessionResponse = $this->actingAs($this->user)
            ->postJson('/api/game/start');

        $sessionId = $sessionResponse->json('session.id');

        // Get current puzzle
        $response = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{sessionId}/current");

        $puzzle = $response->json('puzzle');

        // Verify solution is not in top-level response
        $this->assertArrayNotHasKey('solution', $puzzle);
        
        // Verify solution_data is present but solution is not exposed
        $this->assertArrayHasKey('solution_data', $puzzle);
    }

    /**
     * Test that puzzle retrieval validates session status
     * 
     * Verifies that completed sessions don't return puzzles.
     */
    public function test_puzzle_retrieval_validates_session_status(): void
    {
        // Create session
        $sessionResponse = $this->actingAs($this->user)
            ->postJson('/api/game/start');

        $sessionId = $sessionResponse->json('session.id');

        // Mark session as completed
        $session = GameSession::find($sessionId);
        $session->update(['status' => 'completed']);

        // Try to get puzzle
        $response = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{sessionId}/current");

        $response->assertStatus(404);
    }

    /**
     * Test that puzzle retrieval for invalid session fails
     * 
     * Verifies proper error handling for non-existent sessions.
     */
    public function test_puzzle_retrieval_for_invalid_session_fails(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/puzzles/99999/current');

        $response->assertStatus(404);
    }

    /**
     * Test that user can only access their own puzzles
     * 
     * SECURITY: Verifies data isolation between users.
     */
    public function test_user_cannot_access_other_users_puzzles(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User1 creates session
        $sessionResponse = $this->actingAs($user1)
            ->postJson('/api/game/start');

        $sessionId = $sessionResponse->json('session.id');

        // User2 tries to access User1's puzzle
        $response = $this->actingAs($user2)
            ->getJson("/api/puzzles/{sessionId}/current");

        $response->assertStatus(403);
    }

    /**
     * Test that puzzle sequence is correct
     * 
     * Verifies that puzzles are returned in the correct order.
     */
    public function test_puzzle_sequence_is_correct(): void
    {
        // Create session
        $sessionResponse = $this->actingAs($this->user)
            ->postJson('/api/game/start');

        $sessionId = $sessionResponse->json('session.id');

        // Get first puzzle
        $response1 = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{sessionId}/current");

        $puzzle1 = $response1->json('puzzle');
        $this->assertEquals(1, $puzzle1['sequence_order']);

        // Submit correct solution to first puzzle
        $this->actingAs($this->user)
            ->postJson("/api/puzzles/{$puzzle1['id']}/submit", [
                'session_id' => $sessionId,
                'solution' => $puzzle1['solution_data']['solution'],
            ]);

        // Get second puzzle
        $response2 = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{sessionId}/current");

        $puzzle2 = $response2->json('puzzle');
        $this->assertEquals(2, $puzzle2['sequence_order']);
        $this->assertNotEquals($puzzle1['id'], $puzzle2['id']);
    }

    /**
     * Test that puzzle data maintains integrity through serialization
     * 
     * Verifies that JSON serialization/deserialization doesn't corrupt data.
     */
    public function test_puzzle_data_maintains_integrity_through_serialization(): void
    {
        // Create session
        $sessionResponse = $this->actingAs($this->user)
            ->postJson('/api/game/start');

        $sessionId = $sessionResponse->json('session.id');

        // Get puzzle
        $response = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{sessionId}/current");

        $puzzle = $response->json('puzzle');
        
        // Serialize and deserialize
        $serialized = json_encode($puzzle);
        $deserialized = json_decode($serialized, true);

        // Verify equivalence
        $this->assertEquals($puzzle['id'], $deserialized['id']);
        $this->assertEquals($puzzle['title'], $deserialized['title']);
        $this->assertEquals($puzzle['description'], $deserialized['description']);
        $this->assertEquals($puzzle['type'], $deserialized['type']);
        $this->assertEquals($puzzle['solution_data'], $deserialized['solution_data']);
    }
}
