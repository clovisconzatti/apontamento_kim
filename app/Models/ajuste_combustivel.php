<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ajuste_combustivel extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'data'
        , 'tanque'
        , 'quantidade'
        , 'tipo'

    ];
    protected $primaryKey = 'id';
    protected $table = 'ajuste_combustivel';
}
