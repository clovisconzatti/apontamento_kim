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

    public function servicos()
    {
        return $this->hasMany(tipo_revisao_servico::class, 'tipo_revisao_id')->orderBy('id');
    }

    public function pecas()
    {
        return $this->hasMany(tipo_revisao_peca::class, 'tipo_revisao_id')->orderBy('id');
    }

    public function lubrificantes()
    {
        return $this->hasMany(tipo_revisao_lubrificante::class, 'tipo_revisao_id')->orderBy('id');
    }
}
