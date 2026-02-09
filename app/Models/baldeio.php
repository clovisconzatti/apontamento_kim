<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class baldeio extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'baldeio'
        , 'distancia'

    ];
    protected $primaryKey = 'id';
    protected $table = 'baldeio';
}
