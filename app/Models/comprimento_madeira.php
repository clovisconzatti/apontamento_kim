<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class comprimento_madeira extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'comprimento'

    ];
    protected $primaryKey = 'id';
    protected $table = 'comprimento_madeira';
}
