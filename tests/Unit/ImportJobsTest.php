<?php

use App\Jobs\ImportPlayersJob;
use App\Jobs\ImportTeamsJob;
use Illuminate\Support\Facades\Queue;

test('teams import job can be dispatched', function () {
    Queue::fake();

    ImportTeamsJob::dispatch();

    Queue::assertPushed(ImportTeamsJob::class);
});

test('players import job can be dispatched', function () {
    Queue::fake();

    ImportPlayersJob::dispatch(100, 500);

    Queue::assertPushed(ImportPlayersJob::class, function ($job) {
        return $job->perPage === 100 && $job->limit === 500;
    });
});

test('teams import job has correct configuration', function () {
    $job = new ImportTeamsJob();

    expect($job->tries)->toBe(3)
        ->and($job->timeout)->toBe(300);
});

test('players import job has correct configuration', function () {
    $job = new ImportPlayersJob(50, 200);

    expect($job->tries)->toBe(3)
        ->and($job->timeout)->toBe(600);
});
