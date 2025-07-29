<?php

declare(strict_types=1);

namespace App\Brain\Integration\Tasks;

use App\Brain\Integration\Queries\GetReleasedCollectOrders;
use App\Jobs\SendOrdersToTMS;
use Brain\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Task FetchReleasedCollectOrdersTask
 *
 * @property-read IntegrationConfig $integrationConfig
 * @property Collection $orders
 */
class FetchReleasedCollectOrdersTask extends Task implements ShouldQueue
{
    public function handle(): self
    {
        $this->orders = GetReleasedCollectOrders::run($this->integrationConfig);

        if ($this->orders->isEmpty()) {
            Log::info('No released collect orders found.');
            $this->cancelProcess();
        }

        foreach ($this->orders as $order) {
            SendOrdersToTMS::dispatch($this->integrationConfig, (object) $order);
        }

        return $this;
    }
}
