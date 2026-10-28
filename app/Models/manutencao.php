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
        , 'equipamento'
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

    public function pecas()
    {
        return $this->hasMany(manutencao_peca::class, 'manutencao_id')->orderBy('id');
    }

    public function lubrificantes()
    {
        return $this->hasMany(manutencao_lubrificante::class, 'manutencao_id')->orderBy('id');
    }
}
