<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class colaborador extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'colaborador'
        , 'uf'
        , 'ativo'
        , 'cod'
        , 'empresa'

    ];
    protected $primaryKey = 'id';
    protected $table = 'colaborador';
}
