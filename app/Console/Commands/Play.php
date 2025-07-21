<?php

namespace App\Console\Commands;

use App\Brain\Integration\Processes\IntegrationProcess;
use App\Models\IntegrationConfig;
use Illuminate\Console\Command;

class Play extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'play';

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
        $integrationConfig = IntegrationConfig::query()->first();
        IntegrationProcess::dispatch([
            'integrationConfig' => $integrationConfig,
        ]);
    }
}
