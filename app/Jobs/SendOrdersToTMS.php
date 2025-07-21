<?php

namespace App\Jobs;

use App\Models\IntegrationConfig;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOrdersToTMS implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public IntegrationConfig $integrationConfig,
        public $order,
        public $recipientAddress,
        public $issuerAddress
    ) {
        $this->order = (array) $order; // Ensure order is an array
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $decodedBody = json_decode($this->integrationConfig->body, true);
        // Dados da integração
        $decodedBody['entregas'][0]['cnpjCd'] = $this->integrationConfig->tms_cd_doc;
        $decodedBody['entregas'][0]['codCd'] = (string) $this->integrationConfig->tms_cd_id;

        // Dados do pedido ($order)
        $decodedBody['entregas'][0]['numeroPedido'] = $this->order['numeropedido'] ?? '';
        $decodedBody['entregas'][0]['dataPedido'] = $this->order['datapedido'] ?? '';
        $decodedBody['entregas'][0]['numeroNfe'] = $this->order['numeronfe'] ?? '';
        $decodedBody['entregas'][0]['valorNfe'] = $this->order['valornfe'] ?? '';
        $decodedBody['entregas'][0]['serieNfe'] = $this->order['serie'] ?? '';
        $decodedBody['entregas'][0]['volumeEntrega'] = $this->order['volumeentrega'] ?? '';
        $decodedBody['entregas'][0]['pesoEntrega'] = $this->order['pesoentrega'] ?? '';
        $decodedBody['entregas'][0]['qtdVolumes'] = $this->order['qtdvolumes'] ?? '';
        $decodedBody['entregas'][0]['agrupadorRota'] = $this->order['coleta'] ?? '';
        $decodedBody['entregas'][0]['obsEntrega'] = $this->order['tituloromaneio'] ?? '';
        $decodedBody['entregas'][0]['valorEntrega'] = $this->order['valornfe'] ?? ''; // Supondo que seja igual ao valor da NFe

        // Transportadora
        $decodedBody['entregas'][0]['cnpjTransportadora'] = $this->order['cnpjtransportadora'] ?? '';
        $decodedBody['entregas'][0]['nomeTransportadora'] = $this->order['nometransportadora'] ?? '';

        // Emitente (issuerAddress)
        $decodedBody['entregas'][0]['cnpjCpfEmitente'] = $this->order['cnpjemitente'] ?? '';
        $decodedBody['entregas'][0]['nomeEmitente'] = $this->order['emitente'] ?? '';
        $decodedBody['entregas'][0]['endEmitente'] = $this->issuerAddress->enddes ?? '';
        $decodedBody['entregas'][0]['numEmitente'] = $this->issuerAddress->numdes ?? '';
        $decodedBody['entregas'][0]['cplEmitente'] = $this->issuerAddress->cpldes ?? '';
        $decodedBody['entregas'][0]['baiEmitente'] = $this->issuerAddress->baides ?? '';
        $decodedBody['entregas'][0]['cidEmitente'] = $this->issuerAddress->ciddes ?? '';
        $decodedBody['entregas'][0]['ufEmitente'] = $this->issuerAddress->ufdes ?? '';
        $decodedBody['entregas'][0]['cepEmitente'] = $this->issuerAddress->cep ?? '';

        // Destinatário (recipientAddress)
        $decodedBody['entregas'][0]['cnpjCpfDes'] = $this->integrationConfig->client_doc ?? '';
        $decodedBody['entregas'][0]['endDes'] = $this->recipientAddress->enddes ?? '';
        $decodedBody['entregas'][0]['numDes'] = $this->recipientAddress->numdes ?? '';
        $decodedBody['entregas'][0]['cplDes'] = $this->recipientAddress->cpldes ?? '';
        $decodedBody['entregas'][0]['baiDes'] = $this->recipientAddress->baides ?? '';
        $decodedBody['entregas'][0]['cidDes'] = $this->recipientAddress->ciddes ?? '';
        $decodedBody['entregas'][0]['ufDes'] = $this->recipientAddress->ufdes ?? '';
        $decodedBody['entregas'][0]['cepDes'] = $this->recipientAddress->cep ?? '';
        $decodedBody['entregas'][0]['nomeDes'] = $this->recipientAddress->nomedes ?? '';

        // Remove campo indesejado
        unset($decodedBody['entregas'][0]['id']);

        Log::info([
            'decodedBody' => $decodedBody,
        ]);
    }
}
