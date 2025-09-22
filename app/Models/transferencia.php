<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class transferencia extends Model
{
    use HasFactory;
    // public $timestamps = false;
    protected $fillable= [
        'id'
        , 'origem'
        , 'destino'
        , 'litros'
        , 'nr_doc'
        , 'data'
        ,'combustivel'
        ,'fornecedor'

    ];
    protected $primaryKey = 'id';
    protected $table = 'transferencia';
}
