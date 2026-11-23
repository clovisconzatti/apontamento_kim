<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class manutencao_servico extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'manutencao_id'
        , 'servico'
        , 'mecanico'
        , 'data'
        , 'hora_inicial'
        , 'hora_final'
        , 'valor_hora'
        , 'valor_total'

    ];
    protected $primaryKey = 'id';
    protected $table = 'manutencao_servico';
}
