<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class apontamento extends Model
{
    use HasFactory;
    // public $timestamps = false;
    protected $fillable= [
        'id'
        , 'data'
        , 'equipamento'
        , 'litros'
        , 'km'
        , 'horas'
        , 'combustivel'
        , 'obs'
        , 'origem'

    ];
    protected $primaryKey = 'id';
    protected $table = 'apontamento';
}
