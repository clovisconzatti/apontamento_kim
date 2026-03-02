<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class terreno extends Model
{ use HasFactory;
    protected $fillable= [
        'id'
        , 'terreno'

    ];
    protected $primaryKey = 'id';
    protected $table = 'terreno';
}
