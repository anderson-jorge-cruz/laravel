<?php

namespace App\Console\Commands;

use App\Jobs\RunIntegration;
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
        IntegrationConfig::all()->each(function ($integration) {
            RunIntegration::dispatch($integration);
        });
    }
}
