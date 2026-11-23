<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class revisao_servico extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'revisao_id'
        , 'servico'
        , 'mecanico'
        , 'data'
        , 'hora_inicial'
        , 'hora_final'
        , 'valor_hora'
        , 'valor_total'

    ];
    protected $primaryKey = 'id';
    protected $table = 'revisao_servico';
}
