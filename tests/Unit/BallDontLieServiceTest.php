<?php

use App\Services\BallDontLieService;

beforeEach(function () {
    $this->service = app(BallDontLieService::class);
});

test('transforms team data correctly', function () {
    $apiTeam = [
        'id' => 1,
        'conference' => 'East',
        'division' => 'Atlantic',
        'city' => 'Boston',
        'name' => 'Celtics',
        'full_name' => 'Boston Celtics',
        'abbreviation' => 'BOS',
    ];

    $transformed = $this->service->transformTeamData($apiTeam);

    expect($transformed)->toHaveKeys([
        'external_id',
        'conference',
        'division',
        'city',
        'name',
        'full_name',
        'abbreviation',
    ])
        ->and($transformed['external_id'])->toBe(1)
        ->and($transformed['name'])->toBe('Celtics')
        ->and($transformed['city'])->toBe('Boston')
        ->and($transformed['full_name'])->toBe('Boston Celtics');
});

test('transforms team data with null values', function () {
    $apiTeam = [
        'id' => 1,
        'conference' => null,
        'division' => null,
        'city' => null,
        'name' => 'Test Team',
        'full_name' => 'Test Full Name',
        'abbreviation' => 'TST',
    ];

    $transformed = $this->service->transformTeamData($apiTeam);

    expect($transformed['conference'])->toBeNull()
        ->and($transformed['division'])->toBeNull()
        ->and($transformed['city'])->toBeNull()
        ->and($transformed['name'])->toBe('Test Team');
});

test('transforms player data correctly', function () {
    $apiPlayer = [
        'id' => 237,
        'first_name' => 'LeBron',
        'last_name' => 'James',
        'position' => 'F',
        'height' => '6-9',
        'weight' => '250',
        'jersey_number' => '23',
        'college' => 'St. Vincent-St. Mary HS',
        'country' => 'USA',
        'draft_year' => 2003,
        'draft_round' => 1,
        'draft_number' => 1,
    ];

    $transformed = $this->service->transformPlayerData($apiPlayer, 5);

    expect($transformed)->toHaveKeys([
        'external_id',
        'first_name',
        'last_name',
        'position',
        'height',
        'weight',
        'jersey_number',
        'college',
        'country',
        'draft_year',
        'draft_round',
        'draft_number',
        'team_id',
    ])
        ->and($transformed['external_id'])->toBe(237)
        ->and($transformed['first_name'])->toBe('LeBron')
        ->and($transformed['last_name'])->toBe('James')
        ->and($transformed['team_id'])->toBe(5);
});

test('transforms player data with null values', function () {
    $apiPlayer = [
        'id' => 1,
        'first_name' => 'Test',
        'last_name' => 'Player',
        'position' => null,
        'height' => null,
        'weight' => null,
        'jersey_number' => null,
        'college' => null,
        'country' => null,
        'draft_year' => null,
        'draft_round' => null,
        'draft_number' => null,
    ];

    $transformed = $this->service->transformPlayerData($apiPlayer);

    expect($transformed['position'])->toBeNull()
        ->and($transformed['height'])->toBeNull()
        ->and($transformed['weight'])->toBeNull()
        ->and($transformed['team_id'])->toBeNull()
        ->and($transformed['first_name'])->toBe('Test')
        ->and($transformed['last_name'])->toBe('Player');
});

test('transforms player data without team_id', function () {
    $apiPlayer = [
        'id' => 1,
        'first_name' => 'Test',
        'last_name' => 'Player',
    ];

    $transformed = $this->service->transformPlayerData($apiPlayer);

    expect($transformed['team_id'])->toBeNull();
});

test('transforms player data with team_id', function () {
    $apiPlayer = [
        'id' => 1,
        'first_name' => 'Test',
        'last_name' => 'Player',
    ];

    $transformed = $this->service->transformPlayerData($apiPlayer, 10);

    expect($transformed['team_id'])->toBe(10);
});
