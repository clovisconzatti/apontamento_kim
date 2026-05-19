<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class informacao extends Model
{
    use HasFactory;

    protected $fillable= [
        'id'
        , 'data'
        , 'fazenda'
        , 'equipamento'
        , 'atividade'
        , 'colaborador'
        , 'hora_inicial'
        , 'hora_final'
        , 'horimetro_inicial'
        , 'horimetro_final'
        , 'corte'
        , 'fat_m'
        , 'origem_abastecimento'
        , 'nr_nf'
        , 'qnt_diesel'
        , 'horimetro_abastecimento'
        , 'relogio_tanque_inicial'
        , 'relogio_tanque_final'
        , 'qnt_lubrificante'
        , 'tipo_lubrificante'
        , 'producao_terceiros'
        , 'comprimento_madeira'
        , 'baldeio_curto'
        , 'baldeio_medio'
        , 'baldeio_longo'
        , 'arrasto_curto'
        , 'arrasto_medio'
        , 'arrasto_longo'
        , 'obs'
        , 'clima'
        , 'terreno'
        , 'carg_julieta'
        , 'carg_truck'
        , 'carg_bitrem'
    ];
    protected $primaryKey = 'id';
    protected $table = 'informacao';
}
