<?php

namespace App\Console\Commands;

use App\Jobs\ImportTeamsJob;
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
    protected $description = 'Import NBA teams from BallDontLie API using background job';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Dispatching teams import job...');
        $this->newLine();

        ImportTeamsJob::dispatch();

        $this->line('✓ Teams import job dispatched to queue');
        $this->newLine();
        $this->comment('Run "php artisan queue:work" to process the job');
        $this->comment('Check logs for import progress and results');

        return self::SUCCESS;
    }
}
