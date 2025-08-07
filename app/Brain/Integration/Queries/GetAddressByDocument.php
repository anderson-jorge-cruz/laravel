<?php

declare(strict_types=1);

namespace App\Brain\Integration\Queries;

use App\Models\Silt\ENDERECO;
use Brain\Query;
use Illuminate\Support\Collection;
use stdClass;

class GetAddressByDocument extends Query
{
    public function __construct(
        public string $document
    ) {
        //
    }

    public function handle(): Collection|stdClass|null
    {
        $document = $this->document;

        return ENDERECO::query()
            ->selectRaw('
                LOGRADOURO as endDes,
                NUMERO as numDes,
                CEP as cep,
                COMPLEMENTO as cplDes,
                (SELECT RAZAOSOCIAL FROM WMSPRD.ENTIDADE e2 WHERE e2.IDENTIDADE = e.IDENTIDADE) as nomeDes,
                (SELECT DESCR FROM WMSPRD.BAIRRO WHERE IDBAIRRO = e.IDBAIRRO) as baiDes,
                (SELECT DESCR FROM WMSPRD.CIDADE WHERE IDCIDADE = e.IDCIDADE) as cidDes,
                (SELECT ESTADOCIDADE FROM WMSPRD.CIDADE WHERE IDCIDADE = e.IDCIDADE) as ufDes
            ')
            ->where('IDENTIDADE', function ($query) use ($document) {
                $query->select('IDENTIDADE')
                    ->from('WMSPRD.entidade as e2')
                    ->where('e2.cgc', $document)
                    ->where('e2.ativo', 'S')
                    ->whereIn('e2.tipoentidade', [142, 146])
                    ->first();
            })
            ->getQuery()
            ->first();
    }
}
