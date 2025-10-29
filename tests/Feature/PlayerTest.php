<?php

use App\Models\Player;
use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->admin->generateApiToken();
    $this->adminToken = $this->admin->createToken('admin-token', $this->admin->getAbilities())->plainTextToken;

    $this->user = User::factory()->create(['role' => 'user']);
    $this->user->generateApiToken();
    $this->userToken = $this->user->createToken('user-token', $this->user->getAbilities())->plainTextToken;

    $this->team = Team::factory()->create();
});

test('admin can list all players', function () {
    Player::factory()->count(5)->create(['team_id' => $this->team->id]);

    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->getJson('/api/players');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'external_id', 'first_name', 'last_name', 'position', 'team', 'created_at', 'updated_at'],
            ],
            'links',
            'meta',
        ]);

    expect($response->json('data'))->toHaveCount(5);
});

test('user can list all players', function () {
    Player::factory()->count(3)->create(['team_id' => $this->team->id]);

    $response = $this->withHeaders(headers($this->userToken, $this->user->api_token))
        ->getJson('/api/players');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);
});

test('players can be searched by name', function () {
    Player::factory()->create(['first_name' => 'LeBron', 'last_name' => 'James', 'team_id' => $this->team->id]);
    Player::factory()->create(['first_name' => 'Kevin', 'last_name' => 'Durant', 'team_id' => $this->team->id]);

    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->getJson('/api/players?search=LeBron');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.first_name'))->toBe('LeBron');
});

test('admin can view a single player', function () {
    $player = Player::factory()->create([
        'first_name' => 'LeBron',
        'last_name' => 'James',
        'team_id' => $this->team->id,
    ]);

    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->getJson("/api/players/{$player->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $player->id,
                'first_name' => 'LeBron',
                'last_name' => 'James',
            ],
        ]);
});

test('returns 404 when player not found', function () {
    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->getJson('/api/players/999');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Player not found.',
        ]);
});

test('admin can create a player', function () {
    $playerData = [
        'external_id' => 999,
        'first_name' => 'LeBron',
        'last_name' => 'James',
        'position' => 'F',
        'height' => '6-9',
        'weight' => '250',
        'jersey_number' => '23',
        'college' => 'None',
        'country' => 'USA',
        'draft_year' => 2003,
        'draft_round' => 1,
        'draft_number' => 1,
        'team_id' => $this->team->id,
    ];

    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->postJson('/api/players', $playerData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Player created successfully.',
            'data' => [
                'first_name' => 'LeBron',
                'last_name' => 'James',
            ],
        ]);

    $this->assertDatabaseHas('players', [
        'first_name' => 'LeBron',
        'last_name' => 'James',
        'external_id' => 999,
    ]);
});

test('user can create a player', function () {
    $playerData = [
        'external_id' => 1000,
        'first_name' => 'Kevin',
        'last_name' => 'Durant',
        'position' => 'F',
        'team_id' => $this->team->id,
    ];

    $response = $this->withHeaders(headers($this->userToken, $this->user->api_token))
        ->postJson('/api/players', $playerData);

    $response->assertStatus(201);
    $this->assertDatabaseHas('players', ['first_name' => 'Kevin', 'last_name' => 'Durant']);
});

test('player creation requires first_name and last_name', function () {
    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->postJson('/api/players', [
            'position' => 'F',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name', 'last_name']);
});

test('player creation validates team_id exists', function () {
    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->postJson('/api/players', [
            'first_name' => 'Test',
            'last_name' => 'Player',
            'team_id' => 99999,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['team_id']);
});

test('admin can update a player', function () {
    $player = Player::factory()->create([
        'first_name' => 'OldName',
        'team_id' => $this->team->id,
    ]);

    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->putJson("/api/players/{$player->id}", [
            'first_name' => 'NewName',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Player updated successfully.',
            'data' => [
                'first_name' => 'NewName',
            ],
        ]);

    $this->assertDatabaseHas('players', [
        'id' => $player->id,
        'first_name' => 'NewName',
    ]);
});

test('user can update a player', function () {
    $player = Player::factory()->create(['team_id' => $this->team->id]);

    $response = $this->withHeaders(headers($this->userToken, $this->user->api_token))
        ->putJson("/api/players/{$player->id}", [
            'first_name' => 'Updated',
        ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('players', ['first_name' => 'Updated']);
});

test('admin can delete a player', function () {
    $player = Player::factory()->create(['team_id' => $this->team->id]);

    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->deleteJson("/api/players/{$player->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Player deleted successfully.',
        ]);

    $this->assertDatabaseMissing('players', ['id' => $player->id]);
});

test('user cannot delete a player', function () {
    $player = Player::factory()->create(['team_id' => $this->team->id]);

    $response = $this->withHeaders(headers($this->userToken, $this->user->api_token))
        ->deleteJson("/api/players/{$player->id}");

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'You do not have permission to perform this action.',
        ]);

    $this->assertDatabaseHas('players', ['id' => $player->id]);
});

test('player resource includes team relationship when loaded', function () {
    $player = Player::factory()->create(['team_id' => $this->team->id]);

    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->getJson("/api/players/{$player->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'team' => ['id', 'name', 'full_name', 'abbreviation'],
            ],
        ]);
});
