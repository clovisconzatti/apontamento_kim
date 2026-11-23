<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class revisao_lubrificante extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'revisao_id'
        , 'lubrificante'
        , 'quantidade'
        , 'valor_unitario'
        , 'desconto'
        , 'valor_total'

    ];
    protected $primaryKey = 'id';
    protected $table = 'revisao_lubrificante';
}
