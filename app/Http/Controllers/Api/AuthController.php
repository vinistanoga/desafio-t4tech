<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $abilities = $user->getAbilities();

        $token = $user->createToken(
            name: 'auth-token',
            abilities: $abilities,
            expiresAt: now()->addDays(30)
        )->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
            'abilities' => $abilities,
            'expires_in' => '30 days',
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token_abilities' => $currentToken->abilities,
            'token_name' => $currentToken->name,
            'token_created_at' => $currentToken->created_at,
        ]);
    }

    public function generateXAuthToken(Request $request): JsonResponse
    {
        $apiToken = $request->user()->generateApiToken();

        return $this->successResponse([
            'x_authorization_token' => $apiToken,
        ], 'X-Authorization token generated successfully');
    }

    public function getXAuthToken(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->api_token) {
            return $this->notFoundResponse('No X-Authorization token found. Generate one first.');
        }

        return $this->successResponse([
            'x_authorization_token' => $user->api_token,
        ]);
    }

    public function revokeXAuthToken(Request $request): JsonResponse
    {
        $request->user()->revokeApiToken();

        return $this->successResponse(null, 'X-Authorization token revoked successfully');
    }
}
