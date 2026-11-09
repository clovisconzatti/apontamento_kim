<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Serviço que faz parte de um Tipo de Revisão
class tipo_revisao_servico extends Model
{
    protected $fillable = [
        'id'
        , 'tipo_revisao_id'
        , 'servico'
    ];
    protected $primaryKey = 'id';
    protected $table = 'tipo_revisao_servico';
}
