<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class peca extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'peca'
        , 'cod_cargo'

    ];
    protected $primaryKey = 'id';
    protected $table = 'peca';
}
