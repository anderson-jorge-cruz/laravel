<?php

namespace App\Models\Silt;

use Illuminate\Database\Eloquent\Model;

class ENDERECO extends Model
{
    protected $connection = 'oracle';

    protected $table = 'wmsprd.endereco e';
}
