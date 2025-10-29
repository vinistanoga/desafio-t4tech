<?php

namespace App\Console\Commands;

use App\Jobs\ImportPlayersJob;
use Illuminate\Console\Command;

class ImportPlayers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:players
                            {--limit= : Maximum number of players to import}
                            {--per-page=100 : Players per page (max 100)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import NBA players from BallDontLie API using background job';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $perPage = (int) $this->option('per-page');

        $this->info('Dispatching players import job...');
        $this->newLine();

        ImportPlayersJob::dispatch($perPage, $limit);

        $this->line('✓ Players import job dispatched to queue');
        if ($limit) {
            $this->line("  → Limit: {$limit} players");
        }
        $this->line("  → Per page: {$perPage}");

        $this->newLine();
        $this->comment('Run "php artisan queue:work" to process the job');
        $this->comment('Check logs for import progress and results');

        return self::SUCCESS;
    }
}
