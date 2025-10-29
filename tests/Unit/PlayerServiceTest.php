<?php

use App\Contracts\PlayerRepositoryInterface;
use App\Models\Player;
use App\Models\Team;
use App\Services\PlayerService;

beforeEach(function () {
    $this->playerRepository = app(PlayerRepositoryInterface::class);
    $this->playerService = new PlayerService($this->playerRepository);
    $this->team = Team::factory()->create();
});

test('can get all players', function () {
    Player::factory()->count(3)->create(['team_id' => $this->team->id]);

    $players = $this->playerService->getAllPlayers();

    expect($players)->toHaveCount(3);
});

test('can get paginated players', function () {
    Player::factory()->count(20)->create(['team_id' => $this->team->id]);

    $paginatedPlayers = $this->playerService->getPaginatedPlayers([], 10);

    expect($paginatedPlayers->total())->toBe(20)
        ->and($paginatedPlayers->perPage())->toBe(10)
        ->and($paginatedPlayers->items())->toHaveCount(10);
});

test('can search players by name', function () {
    Player::factory()->create(['first_name' => 'LeBron', 'last_name' => 'James', 'team_id' => $this->team->id]);
    Player::factory()->create(['first_name' => 'Kevin', 'last_name' => 'Durant', 'team_id' => $this->team->id]);

    $players = $this->playerService->getPaginatedPlayers(['search' => 'LeBron']);

    expect($players->total())->toBe(1)
        ->and($players->first()->first_name)->toBe('LeBron');
});

test('can find player by id', function () {
    $player = Player::factory()->create([
        'first_name' => 'LeBron',
        'team_id' => $this->team->id,
    ]);

    $foundPlayer = $this->playerService->findPlayer($player->id);

    expect($foundPlayer)->not->toBeNull()
        ->and($foundPlayer->first_name)->toBe('LeBron');
});

test('returns null when player not found', function () {
    $player = $this->playerService->findPlayer(999);

    expect($player)->toBeNull();
});

test('can create a player', function () {
    $playerData = [
        'external_id' => 1,
        'first_name' => 'LeBron',
        'last_name' => 'James',
        'position' => 'F',
        'height' => '6-9',
        'weight' => '250',
        'jersey_number' => '23',
        'team_id' => $this->team->id,
    ];

    $player = $this->playerService->createPlayer($playerData);

    expect($player->first_name)->toBe('LeBron')
        ->and($player->last_name)->toBe('James');

    $this->assertDatabaseHas('players', ['first_name' => 'LeBron', 'last_name' => 'James']);
});

test('can update a player', function () {
    $player = Player::factory()->create([
        'first_name' => 'OldName',
        'team_id' => $this->team->id,
    ]);

    $updatedPlayer = $this->playerService->updatePlayer($player->id, ['first_name' => 'NewName']);

    expect($updatedPlayer->first_name)->toBe('NewName');
    $this->assertDatabaseHas('players', ['id' => $player->id, 'first_name' => 'NewName']);
});

test('can delete a player', function () {
    $player = Player::factory()->create(['team_id' => $this->team->id]);

    $result = $this->playerService->deletePlayer($player->id);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('players', ['id' => $player->id]);
});

test('delete returns false when player not found', function () {
    $result = $this->playerService->deletePlayer(999);

    expect($result)->toBeFalse();
});

test('player belongs to a team', function () {
    $player = Player::factory()->create(['team_id' => $this->team->id]);

    expect($player->team->id)->toBe($this->team->id);
});
