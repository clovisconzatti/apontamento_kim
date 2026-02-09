<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class lubrificante extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'lubrificante'

    ];
    protected $primaryKey = 'id';
    protected $table = 'lubrificante';
}
