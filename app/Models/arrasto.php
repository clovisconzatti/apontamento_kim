<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class arrasto extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'arrasto'

    ];
    protected $primaryKey = 'id';
    protected $table = 'arrasto';
}
