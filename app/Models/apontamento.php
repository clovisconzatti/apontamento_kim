<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class apontamento extends Model
{
    use HasFactory, SoftDeletes;

    // Listas usadas no cadastro, na edição e nos filtros (mantidas num único lugar)
    const COMBUSTIVEIS = ['Gasolina', 'Etanol', 'Diesel-S10', 'Diesel-S500', 'Arla'];
    const COMBOIOS     = ['Principal', 'Comb.01', 'Comb.02'];

    protected $fillable= [
        'data'
        , 'equipamento'
        , 'litros'
        , 'km'
        , 'horas'
        , 'combustivel'
        , 'obs'
        , 'origem'
        , 'ultimo_km'
        , 'anexo'
    ];
    protected $primaryKey = 'id';
    protected $table = 'apontamento';

    public function equipamentoRel()
    {
        return $this->belongsTo(equipamento::class, 'equipamento', 'id');
    }
}
