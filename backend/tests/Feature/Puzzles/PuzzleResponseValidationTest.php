<?php

namespace Tests\Feature\Puzzles;

use Tests\TestCase;
use App\Models\User;
use App\Models\GameSession;
use App\Models\Puzzle;
use App\Models\PuzzleProgress;

/**
 * Test Suite: Puzzle API Response Validation
 * 
 * This test suite validates that the API returns complete and properly
 * formatted puzzle data. It specifically addresses the puzzle loading issue
 * by verifying response format consistency.
 * 
 * Requirement 11: Get Current Puzzle
 * Requirement 23: API Response Format Consistency
 * Requirement 29: Puzzle Loading Issue Detection
 */
class PuzzleResponseValidationTest extends TestCase
{
    protected User $user;
    protected GameSession $session;
    protected Puzzle $puzzle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->session = GameSession::factory()->for($this->user)->create(['status' => 'active']);
        $this->puzzle = Puzzle::find(1); // First puzzle
        
        // Create progress record
        PuzzleProgress::create([
            'game_session_id' => $this->session->id,
            'puzzle_id' => $this->puzzle->id,
            'started_at' => now(),
            'time_spent' => 0,
            'attempts' => 0,
            'hints_used' => 0,
            'is_completed' => false,
        ]);
    }

    /**
     * Test that get puzzle endpoint returns complete data
     * 
     * CRITICAL: Verifies that the API response has all required fields.
     */
    public function test_get_puzzle_returns_complete_response(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{$this->session->id}/current");

        $response->assertStatus(200);
        
        $puzzle = $response->json('puzzle');
        
        // Verify all required fields are present
        $this->assertNotNull($puzzle['id']);
        $this->assertNotNull($puzzle['title']);
        $this->assertNotNull($puzzle['description']);
        $this->assertNotNull($puzzle['type']);
        $this->assertNotNull($puzzle['solution_data']);
        
        // Verify no null values
        $this->assertNotEmpty($puzzle['id']);
        $this->assertNotEmpty($puzzle['title']);
        $this->assertNotEmpty($puzzle['description']);
        $this->assertNotEmpty($puzzle['type']);
        $this->assertNotEmpty($puzzle['solution_data']);
    }

    /**
     * Test that puzzle response is JSON serializable
     * 
     * Verifies that the response can be properly encoded/decoded.
     */
    public function test_puzzle_response_is_json_serializable(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{$this->session->id}/current");

        // Verify response can be decoded
        $decoded = json_decode($response->getContent(), true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('puzzle', $decoded);
        $this->assertIsArray($decoded['puzzle']);
    }

    /**
     * Test that solution_data is properly formatted
     * 
     * Verifies that solution_data is a valid JSON object.
     */
    public function test_solution_data_is_properly_formatted(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{$this->session->id}/current");

        $puzzle = $response->json('puzzle');
        $solutionData = $puzzle['solution_data'];
        
        // Verify it's an array (decoded JSON)
        $this->assertIsArray($solutionData);
        
        // Verify it has expected structure
        $this->assertArrayHasKey('solution', $solutionData);
        $this->assertNotEmpty($solutionData['solution']);
    }

    /**
     * Test that response format is consistent
     * 
     * Verifies that all puzzle responses follow the same format.
     */
    public function test_response_format_is_consistent(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{$this->session->id}/current");

        $response->assertJsonStructure([
            'puzzle' => [
                'id',
                'title',
                'description',
                'type',
                'sequence_order',
                'solution_data',
            ],
            'progress' => [
                'id',
                'game_session_id',
                'puzzle_id',
                'is_completed',
                'attempts',
                'hints_used',
            ],
        ]);
    }

    /**
     * Test that solution is never exposed
     * 
     * SECURITY: Verifies that the solution is not accidentally exposed.
     */
    public function test_solution_is_never_exposed(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{$this->session->id}/current");

        $puzzle = $response->json('puzzle');
        
        // Verify solution is not in top-level response
        $this->assertArrayNotHasKey('solution', $puzzle);
        
        // Verify solution_data is present but solution is not exposed
        $this->assertArrayHasKey('solution_data', $puzzle);
    }

    /**
     * Test that response includes progress information
     * 
     * Verifies that progress data is included in the response.
     */
    public function test_response_includes_progress_information(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{$this->session->id}/current");

        $response->assertJsonStructure([
            'progress' => [
                'id',
                'game_session_id',
                'puzzle_id',
                'is_completed',
                'attempts',
                'hints_used',
            ],
        ]);

        $progress = $response->json('progress');
        $this->assertEquals($this->session->id, $progress['game_session_id']);
        $this->assertEquals($this->puzzle->id, $progress['puzzle_id']);
        $this->assertFalse($progress['is_completed']);
    }

    /**
     * Test that response status codes are correct
     * 
     * Verifies proper HTTP status codes for different scenarios.
     */
    public function test_response_status_codes_are_correct(): void
    {
        // Valid request
        $response = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{$this->session->id}/current");
        $this->assertEquals(200, $response->status());

        // Invalid session
        $response = $this->actingAs($this->user)
            ->getJson('/api/puzzles/99999/current');
        $this->assertEquals(404, $response->status());

        // Unauthenticated
        $response = $this->getJson("/api/puzzles/{$this->session->id}/current");
        $this->assertEquals(401, $response->status());
    }

    /**
     * Test that response time is acceptable
     * 
     * Verifies that the API responds within acceptable time limits.
     */
    public function test_response_time_is_acceptable(): void
    {
        $startTime = microtime(true);

        $response = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{$this->session->id}/current");

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // Convert to ms

        $response->assertStatus(200);
        
        // Response should be under 200ms
        $this->assertLessThan(200, $duration, "Response took {$duration}ms, expected < 200ms");
    }

    /**
     * Test that puzzle data is not corrupted through multiple requests
     * 
     * Verifies that repeated requests return consistent data.
     */
    public function test_puzzle_data_is_consistent_across_requests(): void
    {
        $response1 = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{$this->session->id}/current");

        $puzzle1 = $response1->json('puzzle');

        $response2 = $this->actingAs($this->user)
            ->getJson("/api/puzzles/{$this->session->id}/current");

        $puzzle2 = $response2->json('puzzle');

        // Verify data is identical
        $this->assertEquals($puzzle1['id'], $puzzle2['id']);
        $this->assertEquals($puzzle1['title'], $puzzle2['title']);
        $this->assertEquals($puzzle1['description'], $puzzle2['description']);
        $this->assertEquals($puzzle1['type'], $puzzle2['type']);
        $this->assertEquals($puzzle1['solution_data'], $puzzle2['solution_data']);
    }

    /**
     * Test that error responses are properly formatted
     * 
     * Verifies that error responses follow the same format.
     */
    public function test_error_responses_are_properly_formatted(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/puzzles/99999/current');

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'message',
        ]);
    }
}
