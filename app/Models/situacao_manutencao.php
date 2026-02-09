<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class situacao_manutencao extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'situacao'

    ];
    protected $primaryKey = 'id';
    protected $table = 'situacao_manutencao';
}
