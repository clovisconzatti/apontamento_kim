<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Peça (com quantidade) que faz parte de um Tipo de Revisão
class tipo_revisao_peca extends Model
{
    protected $fillable = [
        'id'
        , 'tipo_revisao_id'
        , 'peca'
        , 'quantidade'
    ];
    protected $primaryKey = 'id';
    protected $table = 'tipo_revisao_peca';
}
