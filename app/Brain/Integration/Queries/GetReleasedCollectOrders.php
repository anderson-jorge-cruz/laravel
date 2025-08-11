<?php

declare(strict_types=1);

namespace App\Brain\Integration\Queries;

use App\Models\IntegrationConfig;
use App\Models\Silt\VT_ACOMPANHAMENTOSAIDANF;
use Brain\Query;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use stdClass;

class GetReleasedCollectOrders extends Query
{
    public function __construct(
        public IntegrationConfig $integrationConfig
    ) {
        //
    }

    public function handle(): Collection|stdClass|string
    {
        $iddepositante = $this->integrationConfig->iddepositante;
        $client_name = $this->integrationConfig->client_name;
        $client_document = $this->integrationConfig->client_doc;

        return VT_ACOMPANHAMENTOSAIDANF::query()
            ->selectRaw('
                ROW_NUMBER() OVER (ORDER BY 1) AS rn,
                vta.cnpjdepositante,
                TO_CHAR(vta.roteizadoem, \'DD/MM/YYYY HH24:MI:SS\') AS datapedido,
                vta.pedido AS numeropedido,
                vta.notafiscal AS numeronfe,
                vta.idnotafiscal,
                vta.transportadora AS nometransportadora,
                vta.cnpjtransportadora AS cnpjtransportadora,
                vta.entrega,
                vta.emitente,
                vta.serie,
                vta.cnpjemitente,
                vta."H$IDARMAZEM" AS armazem,
                vta."H$TIPOOPER" AS tipooper,
                vta.tituloromaneio,
                vta.vlrtotalnf AS valornfe,
                vta.cubagemm3 AS volumeentrega,
                vta.pesovolumes AS pesoentrega,
                vta.qtdevolumes AS qtdvolumes,
                vta.depositante,
                (
                    SELECT ve.numcoleta
                    FROM wmsprd.v_exportarembarquedet ve
                    WHERE ve.numpedido = vta.pedido
                    AND ve.numnf = vta.notafiscal
                    AND ROWNUM = 1
                ) AS coleta,
                (
                    SELECT ve.idnotafiscal
                    FROM wmsprd.v_exportarembarquedet ve
                    WHERE ve.numpedido = vta.pedido
                    AND ve.numnf = vta.notafiscal
                    AND ROWNUM = 1
                ) AS idnotafiscal
            ')
            ->where('vta.embarqueliberado', 1)
            ->whereIn('vta.pedido', function ($query) use ($iddepositante) {
                $query->selectRaw('DISTINCT ve.numpedido')
                    ->from('wmsprd.v_exportarembarquedet as ve')
                    ->where('ve.iddepositante', $iddepositante)
                    ->whereRaw("ve.dataliberacao >= TO_DATE('".date('Y-m-01')."', 'YYYY-MM-DD')");
            })
            ->whereNotIn('vta.notafiscal', function ($query) use ($client_document) {
                $document = Str::remove(['.', '-', '/'], $client_document);
                $query->select('my.invoice_number')
                    ->from('wmsprd.mytracking as my')
                    ->whereRaw("my.depositante = '$document'");
            })
            ->whereNotNull('vta.tituloromaneio')
            ->whereRaw("vta.tituloromaneio NOT LIKE '%INVENTÁRIO'")
            ->whereRaw("vta.tituloromaneio NOT LIKE '%PEIDDO DE COLETA%'")
            ->whereRaw("vta.tituloromaneio NOT LIKE '%PEDIDO COLETA%'")
            ->where('vta.depositante', 'like', "%{$client_name}%")
            ->getQuery()
            ->get();
    }
}
