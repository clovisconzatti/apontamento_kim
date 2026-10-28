<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class revisao extends Model
{
    use HasFactory;
    protected $fillable= [
        'id'
        , 'data'
        , 'equipamento'
        , 'horimetro_atual'
        , 'tipo_revisao'
        , 'nr_os'
        , 'horimetro_proxima'
        , 'obs'

    ];
    protected $primaryKey = 'id';
    protected $table = 'revisao';

    public function pecas()
    {
        return $this->hasMany(revisao_peca::class, 'revisao_id')->orderBy('id');
    }

    public function lubrificantes()
    {
        return $this->hasMany(revisao_lubrificante::class, 'revisao_id')->orderBy('id');
    }
}
