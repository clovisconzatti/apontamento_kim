<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class corte extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'corte'

    ];
    protected $primaryKey = 'id';
    protected $table = 'corte';
}
