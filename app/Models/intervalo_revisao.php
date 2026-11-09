<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class intervalo_revisao extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'id'
        , 'horas'
    ];
    protected $primaryKey = 'id';
    protected $table = 'intervalo_revisao';
}
