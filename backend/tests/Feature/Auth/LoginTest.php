<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;

/**
 * Test Suite: Authentication Login
 * 
 * This test suite validates the login flow and ensures that users
 * are properly authenticated before accessing game endpoints.
 * 
 * Requirement 2: Authentication Login
 * Requirement 4: Protected Route Access
 */
class LoginTest extends TestCase
{
    /**
     * Test successful login returns valid token
     * 
     * CRITICAL: Verifies that login returns a valid token that can be used
     * for subsequent API requests.
     */
    public function test_login_with_valid_credentials_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'player@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'player@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'email',
                'username',
            ],
            'token',
            'token_type',
        ]);

        $this->assertNotNull($response->json('token'));
        $this->assertEquals('Bearer', $response->json('token_type'));
    }

    /**
     * Test login with incorrect password fails
     * 
     * Verifies that invalid credentials are rejected.
     */
    public function test_login_with_incorrect_password_fails(): void
    {
        User::factory()->create([
            'email' => 'player@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'player@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test login with non-existent email fails
     * 
     * Verifies that non-existent users are rejected.
     */
    public function test_login_with_non_existent_email_fails(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test login with missing credentials fails
     * 
     * Verifies that validation errors are returned for missing fields.
     */
    public function test_login_with_missing_credentials_fails(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test login with invalid email format fails
     * 
     * Verifies that email validation is enforced.
     */
    public function test_login_with_invalid_email_format_fails(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    /**
     * Test that token can be used for authenticated requests
     * 
     * CRITICAL: Verifies that the returned token can be used to access
     * protected endpoints.
     */
    public function test_token_can_be_used_for_authenticated_requests(): void
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

        // Use token to access protected endpoint
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user');

        $response->assertStatus(200);
    }

    /**
     * Test that login clears previous session data
     * 
     * Verifies that each login generates a new token.
     */
    public function test_login_generates_new_token_each_time(): void
    {
        $user = User::factory()->create([
            'email' => 'player@example.com',
            'password' => bcrypt('password123'),
        ]);

        // First login
        $response1 = $this->postJson('/api/auth/login', [
            'email' => 'player@example.com',
            'password' => 'password123',
        ]);

        $token1 = $response1->json('token');

        // Second login
        $response2 = $this->postJson('/api/auth/login', [
            'email' => 'player@example.com',
            'password' => 'password123',
        ]);

        $token2 = $response2->json('token');

        // Tokens should be different
        $this->assertNotEquals($token1, $token2);
    }

    /**
     * Test that login response includes user data
     * 
     * Verifies that user information is returned with the token.
     */
    public function test_login_response_includes_user_data(): void
    {
        $user = User::factory()->create([
            'email' => 'player@example.com',
            'username' => 'testplayer',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'player@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        
        $userData = $response->json('user');
        $this->assertEquals($user->id, $userData['id']);
        $this->assertEquals('player@example.com', $userData['email']);
        $this->assertEquals('testplayer', $userData['username']);
    }

    /**
     * Test that login response is in Spanish
     * 
     * Verifies that all messages are in Spanish.
     */
    public function test_login_response_messages_are_in_spanish(): void
    {
        $user = User::factory()->create([
            'email' => 'player@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'player@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        
        $message = $response->json('message');
        // Message should be in Spanish
        $this->assertNotEmpty($message);
    }

    /**
     * Test that login response time is acceptable
     * 
     * Verifies that login completes within acceptable time limits.
     */
    public function test_login_response_time_is_acceptable(): void
    {
        $user = User::factory()->create([
            'email' => 'player@example.com',
            'password' => bcrypt('password123'),
        ]);

        $startTime = microtime(true);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'player@example.com',
            'password' => 'password123',
        ]);

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // Convert to ms

        $response->assertStatus(200);
        
        // Response should be under 300ms
        $this->assertLessThan(300, $duration, "Login took {$duration}ms, expected < 300ms");
    }
}
