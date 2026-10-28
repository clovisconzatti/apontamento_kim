<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class manutencao_lubrificante extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'manutencao_id'
        , 'lubrificante'
        , 'quantidade'

    ];
    protected $primaryKey = 'id';
    protected $table = 'manutencao_lubrificante';
}
