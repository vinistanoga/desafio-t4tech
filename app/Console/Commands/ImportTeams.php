<?php

namespace App\Console\Commands;

use App\Contracts\TeamRepositoryInterface;
use App\Services\BallDontLieService;
use Illuminate\Console\Command;

class ImportTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:teams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import NBA teams from BallDontLie API';

    public function __construct(
        protected BallDontLieService $ballDontLieService,
        protected TeamRepositoryInterface $teamRepository
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting teams import from BallDontLie API...');
        $this->newLine();

        try {
            $apiTeams = $this->ballDontLieService->getTeams();

            if (empty($apiTeams)) {
                $this->error('No teams found from API.');
                return self::FAILURE;
            }

            $this->info("Found " . count($apiTeams) . " teams. Importing...");

            $progressBar = $this->output->createProgressBar(count($apiTeams));
            $progressBar->start();

            $imported = 0;
            $updated = 0;
            $errors = 0;

            foreach ($apiTeams as $apiTeam) {
                try {
                    $teamData = $this->ballDontLieService->transformTeamData($apiTeam);

                    $existingTeam = $this->teamRepository->findByExternalId($teamData['external_id']);

                    if ($existingTeam) {
                        $this->teamRepository->update($existingTeam->id, $teamData);
                        $updated++;
                    } else {
                        $this->teamRepository->create($teamData);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->error("Error importing team {$apiTeam['full_name']}: {$e->getMessage()}");
                    $progressBar->display();
                    $errors++;
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            $this->info("✅ Teams import completed!");
            $this->table(
                ['Status', 'Count'],
                [
                    ['Imported', $imported],
                    ['Updated', $updated],
                    ['Errors', $errors],
                    ['Total', count($apiTeams)],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to import teams: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
