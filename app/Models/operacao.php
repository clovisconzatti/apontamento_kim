<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class operacao extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'operacao'

    ];
    protected $primaryKey = 'id';
    protected $table = 'operacao';
}
