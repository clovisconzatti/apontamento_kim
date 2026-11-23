<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class manutencao_peca extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'manutencao_id'
        , 'peca'
        , 'quantidade'
        , 'valor_unitario'
        , 'desconto'
        , 'valor_total'

    ];
    protected $primaryKey = 'id';
    protected $table = 'manutencao_peca';
}
