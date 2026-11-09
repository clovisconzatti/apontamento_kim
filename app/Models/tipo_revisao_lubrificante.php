<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Óleo lubrificante (com quantidade) que faz parte de um Tipo de Revisão
class tipo_revisao_lubrificante extends Model
{
    protected $fillable = [
        'id'
        , 'tipo_revisao_id'
        , 'lubrificante'
        , 'quantidade'
    ];
    protected $primaryKey = 'id';
    protected $table = 'tipo_revisao_lubrificante';
}
