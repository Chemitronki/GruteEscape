<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

// Feature: lovecraftian-escape-room, Property 1: Valid Registration Creates Account
// **Validates: Requirements 1.2**
test('Property 1: valid registration creates account with hashed password', function () {
    $iterations = 100;
    
    for ($i = 0; $i < $iterations; $i++) {
        $username = fake()->unique()->userName() . $i;
        $email = fake()->unique()->safeEmail();
        $password = fake()->password(8, 20) . 'Aa1!'; // Ensure it meets requirements
        
        $response = $this->postJson('/api/register', [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'username', 'email'],
                'token',
            ],
            'errors',
        ]);
        
        // Verify user was created in database
        $user = User::where('email', $email)->first();
        expect($user)->not->toBeNull();
        expect($user->username)->toBe($username);
        expect($user->email)->toBe($email);
        
        // Verify password is hashed (not plaintext)
        expect($user->password)->not->toBe($password);
        expect(Hash::check($password, $user->password))->toBeTrue();
    }
});

// Feature: lovecraftian-escape-room, Property 2: Invalid Registration Returns Errors
// **Validates: Requirements 1.3**
test('Property 2: invalid registration returns descriptive errors', function () {
    $iterations = 100;
    
    for ($i = 0; $i < $iterations; $i++) {
        // Create a user first for duplicate testing
        $existingUser = User::factory()->create();
        
        // Test various invalid scenarios
        $invalidScenarios = [
            // Duplicate email
            [
                'username' => fake()->userName() . $i,
                'email' => $existingUser->email,
                'password' => 'ValidPass123!',
                'password_confirmation' => 'ValidPass123!',
            ],
            // Duplicate username
            [
                'username' => $existingUser->username,
                'email' => fake()->unique()->safeEmail(),
                'password' => 'ValidPass123!',
                'password_confirmation' => 'ValidPass123!',
            ],
            // Weak password (too short)
            [
                'username' => fake()->userName() . $i,
                'email' => fake()->unique()->safeEmail(),
                'password' => 'short',
                'password_confirmation' => 'short',
            ],
            // Missing required field
            [
                'username' => fake()->userName() . $i,
                'email' => fake()->unique()->safeEmail(),
                // password missing
            ],
        ];
        
        $scenario = $invalidScenarios[array_rand($invalidScenarios)];
        
        $response = $this->postJson('/api/register', $scenario);
        
        // Should return validation error
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors',
        ]);
        
        // Verify no user was created
        if (isset($scenario['email']) && $scenario['email'] !== $existingUser->email) {
            expect(User::where('email', $scenario['email'])->exists())->toBeFalse();
        }
    }
});

// Feature: lovecraftian-escape-room, Property 3: Valid Login Creates Session
// **Validates: Requirements 1.6**
test('Property 3: valid login creates authenticated session with token', function () {
    $iterations = 100;
    
    for ($i = 0; $i < $iterations; $i++) {
        $password = 'ValidPassword123!';
        $user = User::factory()->create([
            'password' => $password,
        ]);
        
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'username', 'email'],
                'token',
            ],
            'errors',
        ]);
        
        // Verify token was created
        $token = $response->json('data.token');
        expect($token)->not->toBeNull();
        expect($token)->toBeString();
        
        // Verify we can use the token to access protected routes
        $authResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user');
        
        $authResponse->assertStatus(200);
        $authResponse->assertJson([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ],
        ]);
    }
});

// Feature: lovecraftian-escape-room, Property 4: Invalid Login Returns Error
// **Validates: Requirements 1.7**
test('Property 4: invalid login returns authentication error', function () {
    $iterations = 100;
    
    for ($i = 0; $i < $iterations; $i++) {
        $user = User::factory()->create([
            'password' => 'CorrectPassword123!',
        ]);
        
        // Test various invalid scenarios
        $invalidScenarios = [
            // Wrong password
            [
                'email' => $user->email,
                'password' => 'WrongPassword123!',
            ],
            // Non-existent email
            [
                'email' => 'nonexistent' . $i . '@example.com',
                'password' => 'SomePassword123!',
            ],
        ];
        
        $scenario = $invalidScenarios[array_rand($invalidScenarios)];
        
        $response = $this->postJson('/api/login', $scenario);
        
        // Should return authentication error
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertJsonStructure([
            'message',
            'errors',
        ]);
        
        // Verify no token was created
        expect($response->json('data.token'))->toBeNull();
    }
});

// Feature: lovecraftian-escape-room, Property 5: Rate Limiting Blocks Brute Force
// **Validates: Requirements 1.9**
test('Property 5: rate limiting blocks brute force attacks after 5 attempts', function () {
    $iterations = 10;
    
    for ($i = 0; $i < $iterations; $i++) {
        // Clear rate limiter before each test
        RateLimiter::clear('login:test' . $i . '@example.com');
        
        $email = 'test' . $i . '@example.com';
        $user = User::factory()->create([
            'email' => $email,
            'password' => 'CorrectPassword123!',
        ]);
        
        // Make 5 failed login attempts
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $response = $this->postJson('/api/login', [
                'email' => $email,
                'password' => 'WrongPassword' . $attempt,
            ]);
            
            $response->assertStatus(401);
        }
        
        // 6th attempt should be rate limited
        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'WrongPassword6',
        ]);
        
        $response->assertStatus(429);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertJsonStructure([
            'message',
            'errors',
        ]);
        
        // Verify error message mentions rate limiting
        expect($response->json('message'))->toContain('Demasiados intentos');
    }
});

// Feature: lovecraftian-escape-room, Property 28: Password Encryption with Bcrypt
// **Validates: Requirements 1.4, 10.1**
test('Property 28: passwords are encrypted with bcrypt cost factor 10+', function () {
    $iterations = 100;
    
    for ($i = 0; $i < $iterations; $i++) {
        $password = fake()->password(8, 20) . 'Aa1!';
        $username = fake()->unique()->userName() . $i;
        $email = fake()->unique()->safeEmail();
        
        $response = $this->postJson('/api/register', [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);
        
        $response->assertStatus(201);
        
        $user = User::where('email', $email)->first();
        
        // Verify password is hashed
        expect($user->password)->not->toBe($password);
        
        // Verify it's a bcrypt hash (starts with $2y$ or $2a$)
        expect($user->password)->toMatch('/^\$2[ay]\$/');
        
        // Verify bcrypt cost factor is at least 10
        // Bcrypt format: $2y$10$... where 10 is the cost
        preg_match('/^\$2[ay]\$(\d+)\$/', $user->password, $matches);
        $cost = (int) $matches[1];
        expect($cost)->toBeGreaterThanOrEqual(10);
        
        // Verify password can be verified
        expect(Hash::check($password, $user->password))->toBeTrue();
    }
});
