<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class km extends Model
{

    use HasFactory;
    // public $timestamps = false;
    protected $fillable= [
        'id'
        , 'equipamento'
        , 'km'


    ];
    protected $primaryKey = 'id';
    protected $table = 'km';
}
