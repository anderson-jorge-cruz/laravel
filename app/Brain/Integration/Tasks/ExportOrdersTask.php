<?php

declare(strict_types=1);

namespace App\Brain\Integration\Tasks;

use App\Models\OrderExport;
use Brain\Task;
use Illuminate\Support\Facades\DB;

/**
 * Task ExportOrdersTask
 */
class ExportOrdersTask extends Task
{
    public function handle(): self
    {
        $ordersInDatabase = OrderExport::query()->select('idromaneio')->pluck('idromaneio');

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
            ->whereLike('v.DEPOSITANTE', 'MUNICÍPIO DE SÃO JOSÉ DOS PINHAIS')
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
