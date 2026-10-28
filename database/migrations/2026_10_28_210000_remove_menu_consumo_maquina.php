<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveMenuConsumoMaquina extends Migration
{
    // O "Consumo por Máquina" duplicava os painéis de Combustível, Consumo Médio, Lubrificante e Horas Máquina.
    // Exclusão lógica: o endereço antigo leva ao Consumo Médio.
    public function up()
    {
        DB::table('menu')->where('rota', 'painel.consumo_maquina')->whereNull('deleted_at')->update(['deleted_at' => now()]);
    }

    public function down()
    {
        DB::table('menu')->where('rota', 'painel.consumo_maquina')->update(['deleted_at' => null]);
    }
}
