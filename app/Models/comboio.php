<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class comboio extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'tanque'
        , 'capacidade'
        , 'fazenda'
        , 'uf'
        , 'obs'
        , 'ativo'
        , 'tipo'
        , 'saldo_inicial'
        , 'data_saldo_inicial'

    ];
    protected $primaryKey = 'id';
    protected $table = 'comboio';
}
