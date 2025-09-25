<?php

namespace App\Models\Silt;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VT_ACOMPANHAMENTOSAIDANF extends Model
{
    protected $connection = 'oracle';

    protected $table = 'wmsprd.vt_acompanhamentosaidanf1 vta';

    #[Scope]
    protected function esab(Builder $query): void
    {
        $query->where('vta.transportadora', 'SIMAS LOGISTICA LTDA (BH)');
    }
}
