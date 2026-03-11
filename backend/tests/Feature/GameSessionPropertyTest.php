<?php

use App\Models\User;
use App\Models\GameSession;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Feature: lovecraftian-escape-room, Property 6: Game Start Creates Session
// **Validates: Requirements 2.1**
test('game start creates session with active status and countdown timer', function () {
    $iterations = 100;
    
    for ($i = 0; $i < $iterations; $i++) {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/game/start');
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'user_id',
                'started_at',
                'time_remaining',
                'status',
            ],
        ]);
        
        $session = GameSession::where('user_id', $user->id)->first();
        expect($session)->not->toBeNull();
        expect($session->status)->toBe('active');
        expect($session->time_remaining)->toBeGreaterThan(0);
    }
});

// Feature: lovecraftian-escape-room, Property 7: Initial Timer Value
// **Validates: Requirements 2.2**
test('newly created game session has initial timer value of 1500 seconds', function () {
    $iterations = 100;
    
    for ($i = 0; $i < $iterations; $i++) {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/game/start');
        
        $response->assertStatus(201);
        
        $session = GameSession::where('user_id', $user->id)->first();
        expect($session->time_remaining)->toBe(1500);
    }
});

// Feature: lovecraftian-escape-room, Property 8: Timer Decrements Over Time
// **Validates: Requirements 2.3**
test('timer decrements over time for active game session', function () {
    $iterations = 100;
    
    for ($i = 0; $i < $iterations; $i++) {
        $user = User::factory()->create();
        
        // Create a session with a past start time
        $secondsElapsed = fake()->numberBetween(1, 100);
        $session = GameSession::create([
            'user_id' => $user->id,
            'started_at' => now()->subSeconds($secondsElapsed),
            'time_remaining' => 1500,
            'status' => 'active',
        ]);
        
        $token = $user->createToken('test-token')->plainTextToken;
        
        // Sync to get updated time
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/game/sync', [
                'time_remaining' => 1500 - $secondsElapsed,
            ]);
        
        $response->assertStatus(200);
        
        $session->refresh();
        $expectedTime = 1500 - $secondsElapsed;
        
        // Allow 2 second tolerance for test execution time
        expect($session->time_remaining)->toBeLessThanOrEqual($expectedTime);
        expect($session->time_remaining)->toBeGreaterThanOrEqual($expectedTime - 2);
    }
});

// Feature: lovecraftian-escape-room, Property 9: Single Active Session Per User
// **Validates: Requirements 2.9**
test('user can only have one active session at a time', function () {
    $iterations = 100;
    
    for ($i = 0; $i < $iterations; $i++) {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        // Start first session
        $response1 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/game/start');
        $response1->assertStatus(201);
        
        $firstSessionId = $response1->json('data.id');
        
        // Start second session
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/game/start');
        $response2->assertStatus(201);
        
        // Check that only one active session exists
        $activeSessions = GameSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->get();
        
        expect($activeSessions)->toHaveCount(1);
        
        // First session should be abandoned
        $firstSession = GameSession::find($firstSessionId);
        expect($firstSession->status)->toBe('abandoned');
    }
});

// Feature: lovecraftian-escape-room, Property 10: Game Over Prevents Interactions
// **Validates: Requirements 2.6**
test('timed out session prevents completion', function () {
    $iterations = 100;
    
    for ($i = 0; $i < $iterations; $i++) {
        $user = User::factory()->create();
        
        // Create a timed out session
        $session = GameSession::create([
            'user_id' => $user->id,
            'started_at' => now()->subSeconds(1500),
            'time_remaining' => 0,
            'status' => 'timeout',
        ]);
        
        $token = $user->createToken('test-token')->plainTextToken;
        
        // Try to complete the session
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/game/complete', [
                'time_remaining' => 0,
            ]);
        
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
    }
});

// Feature: lovecraftian-escape-room, Property 11: Completion Triggers Victory
// **Validates: Requirements 2.7**
test('completing all puzzles with time remaining triggers victory state', function () {
    $iterations = 100;
    
    for ($i = 0; $i < $iterations; $i++) {
        $user = User::factory()->create();
        
        $timeRemaining = fake()->numberBetween(1, 1500);
        $session = GameSession::create([
            'user_id' => $user->id,
            'started_at' => now(),
            'time_remaining' => $timeRemaining,
            'status' => 'active',
        ]);
        
        $token = $user->createToken('test-token')->plainTextToken;
        
        // Complete the session
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/game/complete', [
                'time_remaining' => $timeRemaining,
            ]);
        
        $response->assertStatus(200);
        
        $session->refresh();
        expect($session->status)->toBe('completed');
        expect($session->completed_at)->not->toBeNull();
    }
});

// Feature: lovecraftian-escape-room, Property 12: Victory Records Completion Time
// **Validates: Requirements 2.8**
test('completed session records completion time', function () {
    $iterations = 100;
    
    for ($i = 0; $i < $iterations; $i++) {
        $user = User::factory()->create();
        
        $timeRemaining = fake()->numberBetween(1, 1500);
        $session = GameSession::create([
            'user_id' => $user->id,
            'started_at' => now(),
            'time_remaining' => $timeRemaining,
            'status' => 'active',
        ]);
        
        $token = $user->createToken('test-token')->plainTextToken;
        
        // Complete the session
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/game/complete', [
                'time_remaining' => $timeRemaining,
            ]);
        
        $response->assertStatus(200);
        
        $session->refresh();
        $expectedCompletionTime = 1500 - $timeRemaining;
        
        expect($session->completion_time)->toBe($expectedCompletionTime);
        expect($session->completion_time)->toBeGreaterThanOrEqual(0);
        expect($session->completion_time)->toBeLessThanOrEqual(1500);
    }
});
