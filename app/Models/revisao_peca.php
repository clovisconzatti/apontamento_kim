<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class revisao_peca extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'revisao_id'
        , 'peca'
        , 'quantidade'

    ];
    protected $primaryKey = 'id';
    protected $table = 'revisao_peca';
}
