<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class servico_revisao extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'id'
        , 'servico'
    ];
    protected $primaryKey = 'id';
    protected $table = 'servico_revisao';
}
