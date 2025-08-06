<?php

namespace App\Jobs;

use App\Brain\Integration\Queries\GetAddressByDocument;
use App\Brain\Integration\Queries\GetAddressByInvoiceId;
use App\Models\IntegrationConfig;
use App\Models\OrderExport;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use stdClass;

class SendOrdersToTMS implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public stdClass $recipientAddress;

    public stdClass $issuerAddress;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public IntegrationConfig $integrationConfig,
        public stdClass $order,
    ) {}

    public $uniqueFor = 3600;

    public function uniqueId(): string
    {
        return "{$this->integrationConfig->id}::{$this->order->numeropedido}::{$this->order->numeronfe}";
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->recipientAddress = GetAddressByInvoiceId::run($this->order->idnotafiscal);
        if (! $this->recipientAddress) {
            Log::warning("No address found for invoice ID: {$this->order->idnotafiscal}");
        }

        $this->issuerAddress = GetAddressByDocument::run($this->order->cnpjemitente);
        if (! $this->issuerAddress) {
            Log::warning("No address found for entity document: {$this->order->cnpjemitente}");
        }

        $decodedBody = json_decode($this->integrationConfig->body, true);
        // Dados da integração
        $decodedBody['entregas'][0]['cnpjCd'] = $this->integrationConfig->tms_cd_doc;
        $decodedBody['entregas'][0]['codCd'] = (string) $this->integrationConfig->tms_cd_id;

        // Dados do pedido ($order)
        $decodedBody['entregas'][0]['numeroPedido'] = $this->order->numeropedido ?? '';
        $decodedBody['entregas'][0]['dataPedido'] = $this->order->datapedido ?? '';
        $decodedBody['entregas'][0]['numeroNfe'] = $this->order->numeronfe ?? '';
        $decodedBody['entregas'][0]['valorNfe'] = $this->order->valornfe ?? '';
        $decodedBody['entregas'][0]['serieNfe'] = $this->order->serie ?? '';
        $decodedBody['entregas'][0]['volumeEntrega'] = $this->order->volumeentrega ?? '';
        $decodedBody['entregas'][0]['pesoEntrega'] = $this->order->pesoentrega ?? '';
        $decodedBody['entregas'][0]['qtdVolumes'] = $this->order->qtdvolumes ?? '';
        $decodedBody['entregas'][0]['agrupadorRota'] = $this->order->coleta ?? '';
        $decodedBody['entregas'][0]['obsEntrega'] = $this->order->tituloromaneio ?? '';
        $decodedBody['entregas'][0]['valorEntrega'] = $this->order->valornfe ?? '';

        // Dados de data esperada de embarque
        $expectedBoardingDate = OrderExport::query()
            ->select(['dataesperadaembarque', 'horaesperadaembarque'])
            ->where('depositante', $this->order->depositante)
            ->where('pedido', $this->order->numeropedido)
            ->first();

        $decodedBody['entregas'][0]['dataPrvEntIni'] = "{$expectedBoardingDate?->dataesperadaembarque} {$expectedBoardingDate?->horaesperadaembarque}";

        // Transportadora
        $decodedBody['entregas'][0]['cnpjTransportadora'] = $this->order->cnpjtransportadora ?? '';
        $decodedBody['entregas'][0]['nomeTransportadora'] = $this->order->nometransportadora ?? '';

        // Emitente (issuerAddress)
        $decodedBody['entregas'][0]['cnpjCpfEmitente'] = $this->order->cnpjemitente ?? '';
        $decodedBody['entregas'][0]['nomeEmitente'] = $this->order->emitente ?? '';
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

        // Configurações de roteirizador
        $decodedBody['entregas'][0]['enviarRoteirizador'] = '1';

        $response = Http::withHeaders([
            'token' => $this->integrationConfig->production_token,
        ])->post($this->integrationConfig->endpoint, $decodedBody);

        if ($response->failed() || json_decode($response->body())->entregas[0]->codMensagem == '2') {
            Log::error("Failed to send order to TMS: {$response->status()} - {$response->body()}");

            return;
        }

        if ($response->successful() && json_decode($response->body())->entregas[0]->codMensagem == '1') {
            $id = DB::connection('oracle')->table('wmsprd.mytracking')->insertGetId([
                'invoice_number' => $this->order->numeronfe,
                'order_number' => $this->order->numeropedido,
                'status_code' => $response->status(),
                'warehouse' => $this->integrationConfig->tms_cd_id,
                'depositante' => Str::remove(['.', '-', '/'], $this->order->cnpjdepositante),
                'coleta' => $this->order->coleta,
            ], 'id');

            DB::connection('mysql')->table('integradorsm.mytracking')->insert([
                'request_id' => $id,
                'integration_id' => $this->integrationConfig->id,
                'request_body' => json_encode($decodedBody),
                'response_body' => $response->body(),
                'sended_to' => $this->integrationConfig->production_token,
            ]);
        }
    }
}
