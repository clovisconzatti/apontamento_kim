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
        ,'data'
        ,'origem'
        ,'destino'
        ,'litros'
        ,'nr_doc'
        ,'combustivel'
        ,'horimetro'
        ,'operador'
        ,'fazenda'
        ,'horimetro_inicial'
        ,'horimetro_final'
        ,'tanque'
        ,'obs'

    ];
    protected $primaryKey = 'id';
    protected $table = 'transferencia';
}
