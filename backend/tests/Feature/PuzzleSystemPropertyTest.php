<?php

use App\Models\GameSession;
use App\Models\Puzzle;
use App\Models\PuzzleProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed puzzles for testing
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\PuzzleSeeder']);
});

// Feature: lovecraftian-escape-room, Property 13: Sequential Puzzle Presentation
test('puzzles are presented in sequential order', function () {
    // Create user and start game session
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $session = GameSession::create([
        'user_id' => $user->id,
        'started_at' => now(),
        'time_remaining' => 1500,
        'status' => 'active',
    ]);

    // Get all puzzles ordered by sequence
    $puzzles = Puzzle::ordered()->get();
    
    // First puzzle should be accessible
    $response = $this->getJson("/api/puzzles/{$session->id}");
    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'data' => [
            'puzzle' => [
                'id' => $puzzles[0]->id,
                'sequence_order' => 1,
            ]
        ]
    ]);

    // Complete first puzzle
    PuzzleProgress::create([
        'game_session_id' => $session->id,
        'puzzle_id' => $puzzles[0]->id,
        'started_at' => now(),
        'completed_at' => now(),
        'is_completed' => true,
        'time_spent' => 60,
        'attempts' => 1,
        'hints_used' => 0,
    ]);

    // Second puzzle should now be accessible
    $response = $this->getJson("/api/puzzles/{$session->id}");
    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'data' => [
            'puzzle' => [
                'id' => $puzzles[1]->id,
                'sequence_order' => 2,
            ]
        ]
    ]);

    // Verify sequential order is maintained
    for ($i = 0; $i < count($puzzles) - 1; $i++) {
        expect($puzzles[$i]->sequence_order)->toBeLessThan($puzzles[$i + 1]->sequence_order);
    }
})->group('puzzle-system');

// Feature: lovecraftian-escape-room, Property 14: Puzzle Completion Unlocks Next
test('completing a puzzle unlocks the next puzzle', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $session = GameSession::create([
        'user_id' => $user->id,
        'started_at' => now(),
        'time_remaining' => 1500,
        'status' => 'active',
    ]);

    $puzzles = Puzzle::ordered()->get();
    
    // Test for first 5 puzzles
    for ($i = 0; $i < 5; $i++) {
        $currentPuzzle = $puzzles[$i];
        
        // Get current puzzle
        $response = $this->getJson("/api/puzzles/{$session->id}");
        $response->assertStatus(200);
        $response->assertJsonPath('data.puzzle.id', $currentPuzzle->id);
        
        // Submit correct solution
        $correctSolution = $this->getCorrectSolution($currentPuzzle);
        $response = $this->postJson("/api/puzzles/{$currentPuzzle->id}/submit", [
            'session_id' => $session->id,
            'solution' => $correctSolution,
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonPath('data.correct', true);
        $response->assertJsonPath('data.puzzle_completed', true);
        
        // Verify puzzle is marked as completed
        $progress = PuzzleProgress::where('game_session_id', $session->id)
            ->where('puzzle_id', $currentPuzzle->id)
            ->first();
        
        expect($progress->is_completed)->toBeTrue();
        expect($progress->completed_at)->not->toBeNull();
        
        // If not the last puzzle, verify next puzzle is now accessible
        if ($i < count($puzzles) - 1) {
            $response = $this->getJson("/api/puzzles/{$session->id}");
            $response->assertStatus(200);
            $response->assertJsonPath('data.puzzle.id', $puzzles[$i + 1]->id);
        }
    }
})->group('puzzle-system');

// Feature: lovecraftian-escape-room, Property 15: Incorrect Solution Provides Feedback
test('incorrect solution provides feedback without revealing answer', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $session = GameSession::create([
        'user_id' => $user->id,
        'started_at' => now(),
        'time_remaining' => 1500,
        'status' => 'active',
    ]);

    $puzzles = Puzzle::ordered()->get();
    
    // Test incorrect solutions for different puzzle types
    foreach ($puzzles as $puzzle) {
        $incorrectSolution = $this->getIncorrectSolution($puzzle);
        
        $response = $this->postJson("/api/puzzles/{$puzzle->id}/submit", [
            'session_id' => $session->id,
            'solution' => $incorrectSolution,
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonPath('data.correct', false);
        
        // Verify feedback is provided
        expect($response->json('data.feedback'))->not->toBeEmpty();
        
        // Verify feedback doesn't contain the solution
        $feedback = $response->json('data.feedback');
        $solution = $puzzle->solution_data['solution'] ?? null;
        
        if ($solution && is_string($solution)) {
            expect(stripos($feedback, $solution))->toBeFalse();
        }
        
        // Verify puzzle remains incomplete
        $progress = PuzzleProgress::where('game_session_id', $session->id)
            ->where('puzzle_id', $puzzle->id)
            ->first();
        
        if ($progress) {
            expect($progress->is_completed)->toBeFalse();
            expect($progress->attempts)->toBeGreaterThan(0);
        }
        
        // Only test first 3 puzzles to keep test fast
        if ($puzzle->sequence_order >= 3) {
            break;
        }
    }
})->group('puzzle-system');

// Feature: lovecraftian-escape-room, Property 16: Puzzle Time Tracking
test('puzzle time is tracked correctly', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $session = GameSession::create([
        'user_id' => $user->id,
        'started_at' => now(),
        'time_remaining' => 1500,
        'status' => 'active',
    ]);

    $puzzle = Puzzle::ordered()->first();
    
    // Get puzzle to start tracking
    $response = $this->getJson("/api/puzzles/{$session->id}");
    $response->assertStatus(200);
    
    $progress = PuzzleProgress::where('game_session_id', $session->id)
        ->where('puzzle_id', $puzzle->id)
        ->first();
    
    expect($progress)->not->toBeNull();
    expect($progress->started_at)->not->toBeNull();
    expect($progress->time_spent)->toBe(0);
    
    // Simulate time passing (2 seconds)
    sleep(2);
    
    // Submit incorrect solution to update time
    $response = $this->postJson("/api/puzzles/{$puzzle->id}/submit", [
        'session_id' => $session->id,
        'solution' => 'WRONG',
    ]);
    
    $response->assertStatus(200);
    
    // Check time was tracked
    $progress->refresh();
    expect($progress->time_spent)->toBeGreaterThanOrEqual(2);
    
    // Get progress endpoint
    $response = $this->getJson("/api/puzzles/{$puzzle->id}/progress?session_id={$session->id}");
    $response->assertStatus(200);
    expect($response->json('data.time_spent'))->toBeGreaterThanOrEqual(2);
})->group('puzzle-system');

/**
 * Helper function to get correct solution for a puzzle
 */
function getCorrectSolution(Puzzle $puzzle)
{
    return match ($puzzle->type) {
        'symbol_cipher' => $puzzle->solution_data['solution'],
        'ritual_pattern' => $puzzle->solution_data['solution'],
        'ancient_lock' => $puzzle->solution_data['solution'],
        'memory_fragments' => ['completed_pairs' => $puzzle->solution_data['pairs']],
        'cosmic_alignment' => array_map(fn($i) => [
            'name' => $puzzle->solution_data['solution'][$i],
            'x' => $puzzle->solution_data['positions'][$i]['x'],
            'y' => $puzzle->solution_data['positions'][$i]['y'],
        ], range(0, count($puzzle->solution_data['solution']) - 1)),
        'tentacle_maze' => ['final_position' => $puzzle->solution_data['exit']],
        'forbidden_tome' => $puzzle->solution_data['solution'],
        'shadow_reflection' => $puzzle->solution_data['solution'],
        'cultist_code' => $puzzle->solution_data['solution'],
        'elder_sign' => [
            'traced_points' => $puzzle->solution_data['points'],
            'lifted' => false,
        ],
        default => null,
    };
}

/**
 * Helper function to get incorrect solution for a puzzle
 */
function getIncorrectSolution(Puzzle $puzzle)
{
    return match ($puzzle->type) {
        'symbol_cipher' => 'WRONGWORD',
        'ritual_pattern' => ['skull', 'chalice', 'dagger', 'tome', 'candle'], // reversed
        'ancient_lock' => '0000',
        'memory_fragments' => ['completed_pairs' => 0],
        'cosmic_alignment' => [['name' => 'wrong', 'x' => 0, 'y' => 0]],
        'tentacle_maze' => ['final_position' => ['x' => 0, 'y' => 0]],
        'forbidden_tome' => [5, 4, 3, 2, 1], // reversed
        'shadow_reflection' => ['down', 'left', 'up', 'right'],
        'cultist_code' => 'WRONGCODE',
        'elder_sign' => [
            'traced_points' => [['x' => 0, 'y' => 0]],
            'lifted' => true,
        ],
        default => 'WRONG',
    };
}
