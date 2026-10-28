<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tipo_revisao extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'descricao'
        , 'intervalo'
        , 'obs'

    ];
    protected $primaryKey = 'id';
    protected $table = 'tipo_revisao';
}
