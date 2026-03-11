<?php

use App\Models\GameSession;
use App\Models\Puzzle;
use App\Models\PuzzleProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed puzzles and hints for testing
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\PuzzleSeeder']);
});

// Feature: lovecraftian-escape-room, Property 17: Hint Availability After Timeout
test('hints become available after 120 seconds without solving puzzle', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $session = GameSession::create([
        'user_id' => $user->id,
        'started_at' => now(),
        'time_remaining' => 1500,
        'status' => 'active',
    ]);

    $puzzle = Puzzle::ordered()->first();
    
    // Create puzzle progress with started_at 2 minutes ago
    $progress = PuzzleProgress::create([
        'game_session_id' => $session->id,
        'puzzle_id' => $puzzle->id,
        'started_at' => now()->subSeconds(120),
        'time_spent' => 0,
        'attempts' => 0,
        'hints_used' => 0,
        'is_completed' => false,
    ]);

    // Check hint availability
    $response = $this->getJson("/api/puzzles/{$puzzle->id}/hints/available");
    $response->assertStatus(200);
    $response->assertJsonPath('data.available', true);
    $response->assertJsonPath('data.hints_used', 0);
    $response->assertJsonPath('data.max_hints', 3);
    expect($response->json('data.time_spent'))->toBeGreaterThanOrEqual(120);
})->group('hint-system');

test('hints are not available before 120 seconds', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $session = GameSession::create([
        'user_id' => $user->id,
        'started_at' => now(),
        'time_remaining' => 1500,
        'status' => 'active',
    ]);

    $puzzle = Puzzle::ordered()->first();
    
    // Create puzzle progress with started_at 1 minute ago (less than 120 seconds)
    $progress = PuzzleProgress::create([
        'game_session_id' => $session->id,
        'puzzle_id' => $puzzle->id,
        'started_at' => now()->subSeconds(60),
        'time_spent' => 0,
        'attempts' => 0,
        'hints_used' => 0,
        'is_completed' => false,
    ]);

    // Check hint availability
    $response = $this->getJson("/api/puzzles/{$puzzle->id}/hints/available");
    $response->assertStatus(200);
    $response->assertJsonPath('data.available', false);
    expect($response->json('data.time_spent'))->toBeLessThan(120);
})->group('hint-system');

test('hints are not available for completed puzzles', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $session = GameSession::create([
        'user_id' => $user->id,
        'started_at' => now(),
        'time_remaining' => 1500,
        'status' => 'active',
    ]);

    $puzzle = Puzzle::ordered()->first();
    
    // Create completed puzzle progress
    $progress = PuzzleProgress::create([
        'game_session_id' => $session->id,
        'puzzle_id' => $puzzle->id,
        'started_at' => now()->subSeconds(200),
        'completed_at' => now(),
        'time_spent' => 180,
        'attempts' => 1,
        'hints_used' => 1,
        'is_completed' => true,
    ]);

    // Check hint availability
    $response = $this->getJson("/api/puzzles/{$puzzle->id}/hints/available");
    $response->assertStatus(200);
    $response->assertJsonPath('data.available', false);
})->group('hint-system');

// Feature: lovecraftian-escape-room, Property 18: Maximum Hints Per Puzzle
test('system provides maximum of 3 hints per puzzle', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $session = GameSession::create([
        'user_id' => $user->id,
        'started_at' => now(),
        'time_remaining' => 1500,
        'status' => 'active',
    ]);

    $puzzle = Puzzle::ordered()->first();
    
    // Create puzzle progress with time elapsed
    $progress = PuzzleProgress::create([
        'game_session_id' => $session->id,
        'puzzle_id' => $puzzle->id,
        'started_at' => now()->subSeconds(150),
        'time_spent' => 0,
        'attempts' => 0,
        'hints_used' => 0,
        'is_completed' => false,
    ]);

    // Request first hint
    $response = $this->getJson("/api/puzzles/{$puzzle->id}/hints/1");
    $response->assertStatus(200);
    $response->assertJsonPath('data.level', 1);
    $response->assertJsonPath('data.hints_used', 1);
    expect($response->json('data.content'))->not->toBeEmpty();

    // Request second hint
    $response = $this->getJson("/api/puzzles/{$puzzle->id}/hints/2");
    $response->assertStatus(200);
    $response->assertJsonPath('data.level', 2);
    $response->assertJsonPath('data.hints_used', 2);

    // Request third hint
    $response = $this->getJson("/api/puzzles/{$puzzle->id}/hints/3");
    $response->assertStatus(200);
    $response->assertJsonPath('data.level', 3);
    $response->assertJsonPath('data.hints_used', 3);

    // Verify hints_used is updated in database
    $progress->refresh();
    expect($progress->hints_used)->toBe(3);

    // Check availability after using all hints
    $response = $this->getJson("/api/puzzles/{$puzzle->id}/hints/available");
    $response->assertStatus(200);
    $response->assertJsonPath('data.available', false);
    $response->assertJsonPath('data.hints_used', 3);
    $response->assertJsonPath('data.max_hints', 3);
})->group('hint-system');

test('requesting hint beyond third is rejected', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $session = GameSession::create([
        'user_id' => $user->id,
        'started_at' => now(),
        'time_remaining' => 1500,
        'status' => 'active',
    ]);

    $puzzle = Puzzle::ordered()->first();
    
    // Create puzzle progress with all hints used
    $progress = PuzzleProgress::create([
        'game_session_id' => $session->id,
        'puzzle_id' => $puzzle->id,
        'started_at' => now()->subSeconds(200),
        'time_spent' => 0,
        'attempts' => 0,
        'hints_used' => 3,
        'is_completed' => false,
    ]);

    // Try to request a fourth hint (invalid level)
    $response = $this->getJson("/api/puzzles/{$puzzle->id}/hints/4");
    $response->assertStatus(400);
    $response->assertJsonPath('success', false);
})->group('hint-system');

test('hints are progressive and more specific', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $session = GameSession::create([
        'user_id' => $user->id,
        'started_at' => now(),
        'time_remaining' => 1500,
        'status' => 'active',
    ]);

    $puzzle = Puzzle::ordered()->first();
    
    // Create puzzle progress
    $progress = PuzzleProgress::create([
        'game_session_id' => $session->id,
        'puzzle_id' => $puzzle->id,
        'started_at' => now()->subSeconds(150),
        'time_spent' => 0,
        'attempts' => 0,
        'hints_used' => 0,
        'is_completed' => false,
    ]);

    // Get all three hints
    $hints = [];
    for ($level = 1; $level <= 3; $level++) {
        $response = $this->getJson("/api/puzzles/{$puzzle->id}/hints/{$level}");
        $response->assertStatus(200);
        $hints[$level] = $response->json('data.content');
    }

    // Verify each hint is different
    expect($hints[1])->not->toBe($hints[2]);
    expect($hints[2])->not->toBe($hints[3]);
    expect($hints[1])->not->toBe($hints[3]);

    // Verify hints increase in length (more specific)
    // This is a heuristic - more specific hints tend to be longer
    expect(strlen($hints[3]))->toBeGreaterThanOrEqual(strlen($hints[1]));
})->group('hint-system');

test('cannot request hint level higher than hints_used plus one', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $session = GameSession::create([
        'user_id' => $user->id,
        'started_at' => now(),
        'time_remaining' => 1500,
        'status' => 'active',
    ]);

    $puzzle = Puzzle::ordered()->first();
    
    // Create puzzle progress with 1 hint used
    $progress = PuzzleProgress::create([
        'game_session_id' => $session->id,
        'puzzle_id' => $puzzle->id,
        'started_at' => now()->subSeconds(150),
        'time_spent' => 0,
        'attempts' => 0,
        'hints_used' => 1,
        'is_completed' => false,
    ]);

    // Try to request hint level 3 (skipping level 2)
    $response = $this->getJson("/api/puzzles/{$puzzle->id}/hints/3");
    $response->assertStatus(403);
    $response->assertJsonPath('success', false);
})->group('hint-system');

test('requesting same hint level multiple times does not increment hints_used', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $session = GameSession::create([
        'user_id' => $user->id,
        'started_at' => now(),
        'time_remaining' => 1500,
        'status' => 'active',
    ]);

    $puzzle = Puzzle::ordered()->first();
    
    // Create puzzle progress
    $progress = PuzzleProgress::create([
        'game_session_id' => $session->id,
        'puzzle_id' => $puzzle->id,
        'started_at' => now()->subSeconds(150),
        'time_spent' => 0,
        'attempts' => 0,
        'hints_used' => 0,
        'is_completed' => false,
    ]);

    // Request first hint
    $response = $this->getJson("/api/puzzles/{$puzzle->id}/hints/1");
    $response->assertStatus(200);
    $response->assertJsonPath('data.hints_used', 1);

    // Request first hint again
    $response = $this->getJson("/api/puzzles/{$puzzle->id}/hints/1");
    $response->assertStatus(200);
    $response->assertJsonPath('data.hints_used', 1);

    // Verify hints_used is still 1
    $progress->refresh();
    expect($progress->hints_used)->toBe(1);
})->group('hint-system');
