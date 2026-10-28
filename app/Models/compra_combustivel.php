<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class compra_combustivel extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'data'
        , 'fornecedor'
        , 'nr_nf'
        , 'combustivel'
        , 'tanque'
        , 'litros'
        , 'valor_litro'
        , 'valor_total'

    ];
    protected $primaryKey = 'id';
    protected $table = 'compra_combustivel';
}
