<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class fornecedor extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'fornecedor'
        , 'cod_cargo'

    ];
    protected $primaryKey = 'id';
    protected $table = 'fornecedor';
}
