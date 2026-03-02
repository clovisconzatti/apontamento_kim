<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tipo_combustivel extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'combustivel'
        , 'unidade'

    ];
    protected $primaryKey = 'id';
    protected $table = 'tipo_combustivel';
}
