<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Log registration attempt
            Log::info('Registration attempt', [
                'email' => $request->email,
                'username' => $request->username,
                'timestamp' => now(),
            ]);

            // Create user (password is automatically hashed by User model)
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            // Create Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Log successful registration
            Log::info('Registration successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'timestamp' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                    ],
                    'token' => $token,
                ],
                'errors' => [],
            ], 201);
        } catch (\Exception $e) {
            // Log registration failure
            Log::error('Registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'timestamp' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar usuario',
                'data' => null,
                'errors' => ['Ocurrió un error inesperado. Por favor, intente nuevamente.'],
            ], 500);
        }
    }

    /**
     * Login a user.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $email = $request->email;
        $rateLimitKey = 'login:' . $email;

        // Check rate limiting (5 attempts per minute)
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            // Log rate limit hit
            Log::warning('Login rate limit exceeded', [
                'email' => $email,
                'timestamp' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Demasiados intentos de inicio de sesión',
                'data' => null,
                'errors' => ["Demasiados intentos. Por favor, intente nuevamente en {$seconds} segundos."],
            ], 429);
        }

        // Log login attempt
        Log::info('Login attempt', [
            'email' => $email,
            'timestamp' => now(),
        ]);

        // Find user by email
        $user = User::where('email', $email)->first();

        // Verify credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Increment rate limiter
            RateLimiter::hit($rateLimitKey, 60);

            // Log failed login
            Log::warning('Login failed - invalid credentials', [
                'email' => $email,
                'timestamp' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas',
                'data' => null,
                'errors' => ['El correo electrónico o la contraseña son incorrectos.'],
            ], 401);
        }

        // Clear rate limiter on successful login
        RateLimiter::clear($rateLimitKey);

        // Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Log successful login
        Log::info('Login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'timestamp' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                ],
                'token' => $token,
            ],
            'errors' => [],
        ], 200);
    }

    /**
     * Logout the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            // Log logout
            Log::info('Logout successful', [
                'user_id' => $request->user()->id,
                'timestamp' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente',
                'data' => null,
                'errors' => [],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'error' => $e->getMessage(),
                'timestamp' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión',
                'data' => null,
                'errors' => ['Ocurrió un error inesperado.'],
            ], 500);
        }
    }

    /**
     * Get the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Usuario autenticado',
            'data' => [
                'user' => [
                    'id' => $request->user()->id,
                    'username' => $request->user()->username,
                    'email' => $request->user()->email,
                ],
            ],
            'errors' => [],
        ], 200);
    }
}
