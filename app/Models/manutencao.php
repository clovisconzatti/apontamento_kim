<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class manutencao extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'data'
        , 'ord_servico'
        , 'fazenda'
        , 'maquina'
        , 'operador'
        , 'hora_inicial'
        , 'hora_final'
        , 'horimetro'
        , 'tipo_manutencao'
        , 'custo'
        , 'manutencao_diaria'
        , 'situacao'
        , 'obs'

    ];
    protected $primaryKey = 'id';
    protected $table = 'manutencao';
}
