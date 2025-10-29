<?php

use App\Models\User;
use function Pest\Laravel\postJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\deleteJson;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'user',
    ]);
});

test('user can login with valid credentials', function () {
    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'user' => ['id', 'name', 'email', 'role'],
                'access_token',
                'token_type',
                'abilities',
                'expires_in',
            ],
            'message',
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'token_type' => 'Bearer',
            ],
        ]);

    expect($response->json('data.access_token'))->not->toBeNull();
});

test('user cannot login with invalid credentials', function () {
    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login requires email and password', function () {
    $response = postJson('/api/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

test('authenticated user can logout', function () {
    $token = $this->user->createToken('test-token', $this->user->getAbilities())->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
        'Accept' => 'application/json',
    ])->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);

    // Verify token is deleted
    expect($this->user->tokens()->count())->toBe(0);
});

test('authenticated user can get their profile', function () {
    $token = $this->user->createToken('test-token', $this->user->getAbilities())->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
        'Accept' => 'application/json',
    ])->getJson('/api/me');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'user' => ['id', 'name', 'email', 'role'],
                'token_abilities',
                'token_name',
                'token_created_at',
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'email' => 'test@example.com',
                    'role' => 'user',
                ],
            ],
        ]);
});

test('admin gets all abilities', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = postJson('/api/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200);
    expect($response->json('data.abilities'))->toBe(['*']);
});

test('regular user gets limited abilities', function () {
    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    $abilities = $response->json('data.abilities');

    expect($abilities)->toContain('player:view', 'player:create', 'team:view')
        ->and($abilities)->not->toContain('player:delete', 'team:delete');
});

test('authenticated user can generate x-authorization token', function () {
    $token = $this->user->createToken('test-token', $this->user->getAbilities())->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
        'Accept' => 'application/json',
    ])->postJson('/api/x-auth/generate');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => ['x_authorization_token'],
            'message',
        ]);

    expect($response->json('data.x_authorization_token'))->not->toBeNull();

    $this->user->refresh();
    expect($this->user->api_token)->not->toBeNull();
});

test('authenticated user can get their x-authorization token', function () {
    $this->user->generateApiToken();
    $token = $this->user->createToken('test-token', $this->user->getAbilities())->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
        'Accept' => 'application/json',
    ])->getJson('/api/x-auth/token');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'x_authorization_token' => $this->user->api_token,
            ],
        ]);
});

test('authenticated user can revoke x-authorization token', function () {
    $this->user->generateApiToken();
    $token = $this->user->createToken('test-token', $this->user->getAbilities())->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
        'Accept' => 'application/json',
    ])->deleteJson('/api/x-auth/revoke');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'X-Authorization token revoked successfully',
        ]);

    $this->user->refresh();
    expect($this->user->api_token)->toBeNull();
});

test('unauthenticated user cannot access protected routes', function () {
    getJson('/api/me')->assertStatus(401);
    postJson('/api/logout')->assertStatus(401);
    postJson('/api/x-auth/generate')->assertStatus(401);
});
