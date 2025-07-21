<?php

declare(strict_types=1);

namespace App\Brain\Integration\Tasks;

use App\Brain\Integration\Queries\GetAddressByDocument;
use App\Brain\Integration\Queries\GetAddressByInvoiceId;
use App\Brain\Integration\Queries\GetReleasedCollectOrders;
use App\Jobs\SendOrdersToTMS;
use Brain\Task;
use Illuminate\Support\Facades\Log;

/**
 * Task FetchReleasedCollectOrdersTask
 *
 * @property-read IntegrationConfig $integrationConfig
 * @property Collection $orders
 */
class FetchReleasedCollectOrdersTask extends Task
{
    public function handle(): self
    {
        $this->orders = GetReleasedCollectOrders::run($this->integrationConfig);

        if ($this->orders->isEmpty()) {
            Log::info('No released collect orders found.');
            $this->cancelProcess();
        }

        foreach ($this->orders as $order) {
            $recipientAddress = GetAddressByInvoiceId::run(13627250);
            if (! $recipientAddress) {
                Log::warning("No address found for invoice ID: {$order->idnotafiscal}");

                continue;
            }

            $issuerAddress = GetAddressByDocument::run($order->cnpjemitente);
            if (! $issuerAddress) {
                Log::warning("No address found for entity document: {$order->cnpjemitente}");

                continue;
            }

            SendOrdersToTMS::dispatch($this->integrationConfig, $order, $recipientAddress, $issuerAddress);
        }

        return $this;
    }
}
