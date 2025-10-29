<?php

namespace App\Console\Commands;

use App\Contracts\PlayerRepositoryInterface;
use App\Contracts\TeamRepositoryInterface;
use App\Services\BallDontLieService;
use Illuminate\Console\Command;

class ImportPlayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:players
                            {--limit=500 : Maximum number of players to import}
                            {--per-page=100 : Players per page (max 100)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import NBA players from BallDontLie API';

    public function __construct(
        protected BallDontLieService $ballDontLieService,
        protected PlayerRepositoryInterface $playerRepository,
        protected TeamRepositoryInterface $teamRepository
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $perPage = min((int) $this->option('per-page'), 100); // Max 100

        $this->info("Starting players import from BallDontLie API...");
        $this->info("Will import up to {$limit} players ({$perPage} per request)");
        $this->newLine();

        $totalImported = 0;
        $totalUpdated = 0;
        $totalErrors = 0;
        $cursor = null;
        $requestCount = 0;

        try {
            do {
                $requestCount++;
                $this->info("Request #{$requestCount} (cursor: " . ($cursor ?? 'first page') . ")");

                $result = $this->ballDontLieService->getPlayers($cursor, $perPage);
                $apiPlayers = $result['data'] ?? [];
                $meta = $result['meta'] ?? [];

                if (empty($apiPlayers)) {
                    $this->warn("No more players found. Import completed.");
                    break;
                }

                $this->info("Fetched " . count($apiPlayers) . " players");

                $progressBar = $this->output->createProgressBar(count($apiPlayers));
                $progressBar->start();

                foreach ($apiPlayers as $apiPlayer) {
                    // Stop if limit reached
                    if (($totalImported + $totalUpdated) >= $limit) {
                        $progressBar->finish();
                        $this->newLine();
                        $this->info("Limit of {$limit} players reached. Stopping.");
                        break 2;
                    }

                    try {
                        $teamId = null;
                        if (!empty($apiPlayer['team']['id'])) {
                            $team = $this->teamRepository->findByExternalId($apiPlayer['team']['id']);
                            $teamId = $team?->id;
                        }

                        $playerData = $this->ballDontLieService->transformPlayerData($apiPlayer, $teamId);

                        $existingPlayer = $this->playerRepository->findByExternalId($playerData['external_id']);

                        if ($existingPlayer) {
                            $this->playerRepository->update($existingPlayer->id, $playerData);
                            $totalUpdated++;
                        } else {
                            $this->playerRepository->create($playerData);
                            $totalImported++;
                        }
                    } catch (\Exception $e) {
                        $totalErrors++;
                        $this->newLine();
                        $this->error("Error importing player {$apiPlayer['first_name']} {$apiPlayer['last_name']}: {$e->getMessage()}");
                        $progressBar->display();
                    }

                    $progressBar->advance();
                }

                $progressBar->finish();
                $this->newLine(2);

                $cursor = $meta['next_cursor'] ?? null;

            } while ($cursor !== null);

            $this->info("✅ Players import completed!");
            $this->table(
                ['Status', 'Count'],
                [
                    ['Imported', $totalImported],
                    ['Updated', $totalUpdated],
                    ['Errors', $totalErrors],
                    ['Total Processed', $totalImported + $totalUpdated],
                    ['API Requests', $requestCount],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to import players: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
