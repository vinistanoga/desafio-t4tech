<?php

namespace App\Jobs;

use App\Services\BallDontLieService;
use App\Services\PlayerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Player;
use App\Models\Team;

class ImportPlayersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600;

    protected int $perPage;
    protected ?int $limit;

    public function __construct(int $perPage = 100, ?int $limit = null)
    {
        $this->perPage = $perPage;
        $this->limit = $limit;
    }

    /**
     * Execute the job.
     */
    public function handle(BallDontLieService $ballDontLieService, PlayerService $playerService): void
    {
        Log::info('Starting players import job', [
            'per_page' => $this->perPage,
            'limit' => $this->limit
        ]);

        try {
            $cursor = null;
            $totalImported = 0;
            $totalUpdated = 0;
            $totalErrors = 0;
            $totalProcessed = 0;

            do {
                $response = $ballDontLieService->getPlayers($cursor, $this->perPage);
                $apiPlayers = $response['data'];
                $cursor = $response['meta']['next_cursor'] ?? null;

                foreach ($apiPlayers as $apiPlayer) {
                    try {
                        $teamId = null;
                        if (!empty($apiPlayer['team']['id'])) {
                            $team = Team::where('external_id', $apiPlayer['team']['id'])->first();
                            $teamId = $team?->id;
                        }

                        $playerData = $ballDontLieService->transformPlayerData($apiPlayer, $teamId);

                        $existingPlayer = Player::where('external_id', $playerData['external_id'])->first();

                        if ($existingPlayer) {
                            $playerService->updatePlayer($existingPlayer->id, $playerData);
                            $totalUpdated++;
                        } else {
                            $playerService->createPlayer($playerData);
                            $totalImported++;
                        }

                        $totalProcessed++;

                        if ($this->limit && $totalProcessed >= $this->limit) {
                            break 2;
                        }
                    } catch (\Exception $e) {
                        $totalErrors++;
                        Log::error("Error importing player {$apiPlayer['first_name']} {$apiPlayer['last_name']}: {$e->getMessage()}");
                    }
                }
            } while ($cursor !== null && (!$this->limit || $totalProcessed < $this->limit));

            Log::info("Players import completed", [
                'imported' => $totalImported,
                'updated' => $totalUpdated,
                'errors' => $totalErrors,
                'total_processed' => $totalProcessed
            ]);
        } catch (\Exception $e) {
            Log::error("Players import job failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Players import job failed permanently: {$exception->getMessage()}");
    }
}
