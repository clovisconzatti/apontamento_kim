<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class equipamento extends Model
{
    use HasFactory;
    // public $timestamps = false;
    protected $fillable= [
        'id'
        , 'placa'
        , 'equipamento'


    ];
    protected $primaryKey = 'id';
    protected $table = 'equipamento';
}
