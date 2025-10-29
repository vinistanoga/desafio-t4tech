<?php

use App\Contracts\TeamRepositoryInterface;
use App\Models\Team;
use App\Services\TeamService;

beforeEach(function () {
    $this->teamRepository = app(TeamRepositoryInterface::class);
    $this->teamService = new TeamService($this->teamRepository);
});

test('can get all teams', function () {
    Team::factory()->count(3)->create();

    $teams = $this->teamService->getAllTeams();

    expect($teams)->toHaveCount(3);
});

test('can get paginated teams', function () {
    Team::factory()->count(20)->create();

    $paginatedTeams = $this->teamService->getPaginatedTeams([], 10);

    expect($paginatedTeams->total())->toBe(20)
        ->and($paginatedTeams->perPage())->toBe(10)
        ->and($paginatedTeams->items())->toHaveCount(10);
});

test('can filter teams by conference', function () {
    Team::factory()->count(2)->create(['conference' => 'East']);
    Team::factory()->count(3)->create(['conference' => 'West']);

    $eastTeams = $this->teamService->getPaginatedTeams(['conference' => 'East']);

    expect($eastTeams->total())->toBe(2);
});

test('can filter teams by division', function () {
    Team::factory()->count(2)->create(['division' => 'Atlantic']);
    Team::factory()->count(3)->create(['division' => 'Pacific']);

    $atlanticTeams = $this->teamService->getPaginatedTeams(['division' => 'Atlantic']);

    expect($atlanticTeams->total())->toBe(2);
});

test('can find team by id', function () {
    $team = Team::factory()->create(['name' => 'Lakers']);

    $foundTeam = $this->teamService->findTeam($team->id);

    expect($foundTeam)->not->toBeNull()
        ->and($foundTeam->name)->toBe('Lakers');
});

test('returns null when team not found', function () {
    $team = $this->teamService->findTeam(999);

    expect($team)->toBeNull();
});

test('can create a team', function () {
    $teamData = [
        'external_id' => 1,
        'conference' => 'East',
        'division' => 'Atlantic',
        'city' => 'Boston',
        'name' => 'Celtics',
        'full_name' => 'Boston Celtics',
        'abbreviation' => 'BOS',
    ];

    $team = $this->teamService->createTeam($teamData);

    expect($team->name)->toBe('Celtics')
        ->and($team->city)->toBe('Boston');

    $this->assertDatabaseHas('teams', ['name' => 'Celtics']);
});

test('can update a team', function () {
    $team = Team::factory()->create(['name' => 'OldName']);

    $updatedTeam = $this->teamService->updateTeam($team->id, ['name' => 'NewName']);

    expect($updatedTeam->name)->toBe('NewName');
    $this->assertDatabaseHas('teams', ['id' => $team->id, 'name' => 'NewName']);
});

test('can delete a team', function () {
    $team = Team::factory()->create();

    $result = $this->teamService->deleteTeam($team->id);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('teams', ['id' => $team->id]);
});

test('delete returns false when team not found', function () {
    $result = $this->teamService->deleteTeam(999);

    expect($result)->toBeFalse();
});

test('can filter teams by multiple criteria', function () {
    Team::factory()->create(['conference' => 'East', 'division' => 'Atlantic']);
    Team::factory()->create(['conference' => 'East', 'division' => 'Central']);
    Team::factory()->create(['conference' => 'West', 'division' => 'Atlantic']);

    $teams = $this->teamService->getPaginatedTeams([
        'conference' => 'East',
        'division' => 'Atlantic',
    ]);

    expect($teams->total())->toBe(1);
});
