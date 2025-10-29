<?php

namespace App\Jobs;

use App\Services\BallDontLieService;
use App\Services\TeamService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Team;

class ImportTeamsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    public function handle(BallDontLieService $ballDontLieService, TeamService $teamService): void
    {
        Log::info('Starting teams import job');

        try {
            $teamsData = $ballDontLieService->getTeams();

            $imported = 0;
            $updated = 0;
            $errors = 0;

            foreach ($teamsData as $apiTeam) {
                try {
                    $teamData = $ballDontLieService->transformTeamData($apiTeam);

                    $existingTeam = Team::where('external_id', $teamData['external_id'])->first();

                    if ($existingTeam) {
                        $teamService->updateTeam($existingTeam->id, $teamData);
                        $updated++;
                    } else {
                        $teamService->createTeam($teamData);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    Log::error("Error importing team {$apiTeam['name']}: {$e->getMessage()}");
                }
            }

            Log::info("Teams import completed", [
                'imported' => $imported,
                'updated' => $updated,
                'errors' => $errors,
                'total' => count($teamsData)
            ]);
        } catch (\Exception $e) {
            Log::error("Teams import job failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Teams import job failed permanently: {$exception->getMessage()}");
    }
}
