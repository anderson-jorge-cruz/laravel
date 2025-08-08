<?php

namespace App\Console\Commands;

use App\Brain\Integration\Processes\IntegrationProcess;
use App\Models\IntegrationConfig;
use Illuminate\Console\Command;

class Integrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:integrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        IntegrationConfig::where('is_active', 1)->each(function ($integration) {
            IntegrationProcess::dispatch([
                'integrationConfig' => $integration,
            ]);
        });
    }
}
