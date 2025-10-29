<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

/**
 * Create an authenticated admin user.
 */
function createAdmin(array $attributes = []): \App\Models\User
{
    return \App\Models\User::factory()->create(array_merge([
        'role' => 'admin',
    ], $attributes));
}

/**
 * Create an authenticated regular user.
 */
function createUser(array $attributes = []): \App\Models\User
{
    return \App\Models\User::factory()->create(array_merge([
        'role' => 'user',
    ], $attributes));
}

/**
 * Create a Sanctum token for a user.
 */
function actingAsAdmin(): \App\Models\User
{
    $admin = createAdmin();
    $admin->generateApiToken();
    return $admin;
}

/**
 * Create a Sanctum token for a regular user.
 */
function actingAsUser(): \App\Models\User
{
    $user = createUser();
    $user->generateApiToken();
    return $user;
}

/**
 * Generate authentication headers for API requests.
 */
function headers(?string $token = null, ?string $apiToken = null): array
{
    $headers = [];

    if ($token) {
        $headers['Authorization'] = 'Bearer ' . $token;
    }

    if ($apiToken) {
        $headers['X-Authorization'] = $apiToken;
    }

    return $headers;
}
