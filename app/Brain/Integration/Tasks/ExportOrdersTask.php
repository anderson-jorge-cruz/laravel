<?php

declare(strict_types=1);

namespace App\Brain\Integration\Tasks;

use App\Models\OrderExport;
use Brain\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

/**
 * Task ExportOrdersTask
 *
 * @property-read IntegrationConfig $integrationConfig
 */
class ExportOrdersTask extends Task implements ShouldQueue
{
    public function handle(): self
    {
        $ordersInDatabase = OrderExport::query()
            ->select('idromaneio')
            ->where('depositante', $this->integrationConfig->client_name)
            ->whereCreatedAt('>=', date('Y-m-01'))
            ->pluck('idromaneio');

        $exportOrders = DB::connection('oracle')
            ->table('WMSPRD.VT_GERENCIADOREXPEDICAO v')
            ->select([
                'v.IDROMANEIO',
                'v.CODROMANEIO',
                'v.PEDIDO',
                'v.NOTAFISCAL',
                'v.DATAESPERADAEMBARQUE',
                'v.HORAESPERADAEMBARQUE',
                'v.DEPOSITANTE',
            ])
            ->whereNotIn('IDROMANEIO', $ordersInDatabase)
            ->whereLike('v.DEPOSITANTE', $this->integrationConfig->client_name)
            ->whereNotNull('v.DATAESPERADAEMBARQUE')
            ->whereRaw("v.DTGERACAO >= TO_DATE('".date('Y-m-01')."', 'YYYY-MM-DD')")
            ->get();

        if ($exportOrders->isNotEmpty()) {
            foreach ($exportOrders as $order) {
                OrderExport::query()->create((array) $order);
            }
        }

        return $this;
    }
}
