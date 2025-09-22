<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class combustivel extends Model
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

    ];
    protected $primaryKey = 'id';
    protected $table = 'combustivel';
}
