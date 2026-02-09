<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class equipamento extends Model
{
    use HasFactory;
    // public $timestamps = false;
    protected $fillable= [
        'id'
        , 'equipamento'
        , 'ano'
        , 'ativo'
        , 'uf'
        , 'data_partida'
        , 'tipo'
        , 'atividade'
        , 'cilindros'
        , 'operacao'
        , 'consumo_minimo'
        , 'consumo_maximo'
        , 'placa'


    ];
    protected $primaryKey = 'id';
    protected $table = 'equipamento';
}
