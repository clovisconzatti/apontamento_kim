<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tipo extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'tipo'

    ];
    protected $primaryKey = 'id';
    protected $table = 'tipo';
}
