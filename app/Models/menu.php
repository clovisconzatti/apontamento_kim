<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class menu extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable= [
        'id'
        , 'ordem'
        , 'descricao'
        , 'tipo'
        , 'rota'
        , 'icone'

    ];
    protected $primaryKey = 'id';
    protected $table = 'menu';

}
