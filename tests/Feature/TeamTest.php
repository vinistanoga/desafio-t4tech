<?php

use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->admin->generateApiToken();
    $this->adminToken = $this->admin->createToken('admin-token', $this->admin->getAbilities())->plainTextToken;

    $this->user = User::factory()->create(['role' => 'user']);
    $this->user->generateApiToken();
    $this->userToken = $this->user->createToken('user-token', $this->user->getAbilities())->plainTextToken;
});

test('admin can list all teams', function () {
    Team::factory()->count(5)->create();

    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->getJson('/api/teams');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'external_id', 'conference', 'division', 'city', 'name', 'full_name', 'abbreviation', 'created_at', 'updated_at'],
            ],
            'links',
            'meta',
        ]);

    expect($response->json('data'))->toHaveCount(5);
});

test('user can list all teams', function () {
    Team::factory()->count(3)->create();

    $response = $this->withHeaders(headers($this->userToken, $this->user->api_token))
        ->getJson('/api/teams');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);
});

test('teams can be filtered by conference', function () {
    Team::factory()->create(['conference' => 'East']);
    Team::factory()->create(['conference' => 'West']);
    Team::factory()->create(['conference' => 'East']);

    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->getJson('/api/teams?conference=East');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(2);
});

test('teams can be filtered by division', function () {
    Team::factory()->create(['division' => 'Atlantic']);
    Team::factory()->create(['division' => 'Pacific']);
    Team::factory()->create(['division' => 'Atlantic']);

    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->getJson('/api/teams?division=Atlantic');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(2);
});

test('admin can view a single team', function () {
    $team = Team::factory()->create(['name' => 'Lakers']);

    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->getJson("/api/teams/{$team->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $team->id,
                'name' => 'Lakers',
            ],
        ]);
});

test('returns 404 when team not found', function () {
    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->getJson('/api/teams/999');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Team not found.',
        ]);
});

test('admin can create a team', function () {
    $teamData = [
        'external_id' => 99,
        'conference' => 'East',
        'division' => 'Atlantic',
        'city' => 'Boston',
        'name' => 'Celtics',
        'full_name' => 'Boston Celtics',
        'abbreviation' => 'BOS',
    ];

    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->postJson('/api/teams', $teamData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Team created successfully.',
            'data' => [
                'name' => 'Celtics',
                'city' => 'Boston',
            ],
        ]);

    $this->assertDatabaseHas('teams', [
        'name' => 'Celtics',
        'external_id' => 99,
    ]);
});

test('user can create a team', function () {
    $teamData = [
        'external_id' => 100,
        'conference' => 'West',
        'division' => 'Pacific',
        'city' => 'Los Angeles',
        'name' => 'Lakers',
        'full_name' => 'Los Angeles Lakers',
        'abbreviation' => 'LAL',
    ];

    $response = $this->withHeaders(headers($this->userToken, $this->user->api_token))
        ->postJson('/api/teams', $teamData);

    $response->assertStatus(201);
    $this->assertDatabaseHas('teams', ['name' => 'Lakers']);
});

test('team creation requires name and full_name', function () {
    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->postJson('/api/teams', [
            'city' => 'Boston',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'full_name', 'abbreviation']);
});

test('admin can update a team', function () {
    $team = Team::factory()->create(['name' => 'OldName']);

    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->putJson("/api/teams/{$team->id}", [
            'name' => 'NewName',
            'full_name' => 'New Full Name',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Team updated successfully.',
            'data' => [
                'name' => 'NewName',
            ],
        ]);

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'name' => 'NewName',
    ]);
});

test('user can update a team', function () {
    $team = Team::factory()->create(['name' => 'OldName']);

    $response = $this->withHeaders(headers($this->userToken, $this->user->api_token))
        ->putJson("/api/teams/{$team->id}", [
            'name' => 'UpdatedName',
        ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('teams', ['name' => 'UpdatedName']);
});

test('admin can delete a team', function () {
    $team = Team::factory()->create();

    $response = $this->withHeaders(headers($this->adminToken, $this->admin->api_token))
        ->deleteJson("/api/teams/{$team->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Team deleted successfully.',
        ]);

    $this->assertDatabaseMissing('teams', ['id' => $team->id]);
});

test('user cannot delete a team', function () {
    $team = Team::factory()->create();

    $response = $this->withHeaders(headers($this->userToken, $this->user->api_token))
        ->deleteJson("/api/teams/{$team->id}");

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'You do not have permission to perform this action.',
        ]);

    $this->assertDatabaseHas('teams', ['id' => $team->id]);
});

test('unauthenticated user cannot access teams', function () {
    $this->getJson('/api/teams')->assertStatus(401);
    $this->postJson('/api/teams', [])->assertStatus(401);
});

test('request without x-authorization header is rejected', function () {
    $response = $this->withHeaders([
        'Authorization' => "Bearer {$this->adminToken}",
        'Accept' => 'application/json',
    ])->getJson('/api/teams');

    $response->assertStatus(401);
});
