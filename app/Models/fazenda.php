<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class fazenda extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'fazenda'
        , 'uf'
        , 'apontador'
        , 'ativa'

    ];
    protected $primaryKey = 'id';
    protected $table = 'fazenda';
}
