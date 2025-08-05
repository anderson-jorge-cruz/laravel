<?php

namespace App\Console\Commands;

use App\Brain\Integration\Queries\GetReleasedCollectOrders;
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
        $query = GetReleasedCollectOrders::run(
            IntegrationConfig::find(1)
        );

        dd($query);
    }
}
