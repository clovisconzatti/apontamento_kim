<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MenuPainelConsumoMedio extends Migration
{
    // Painel de Operações · Consumo Médio (logo depois de Consumo Combustível), liberado para quem já acessa o Painel de Operações
    public function up()
    {
        if (DB::table('menu')->where('rota', 'painel.consumo_medio')->whereNull('deleted_at')->exists()) {
            return;
        }
        $menuId = DB::table('menu')->insertGetId([
            'ordem'     => '04.0051',
            'descricao' => 'Consumo Médio',
            'tipo'      => 'Link',
            'rota'      => 'painel.consumo_medio',
            'icone'     => 'fas fa-tachometer-alt',
        ]);

        $vizinho = DB::table('menu')->where('rota', 'painel.combustivel')->whereNull('deleted_at')->value('id');
        $usuarios = DB::table('menuUsuario')->where('menuId', $vizinho)->whereNull('deleted_at')->pluck('usuarioId');
        foreach ($usuarios as $usuarioId) {
            DB::table('menuUsuario')->insert(['usuarioId' => $usuarioId, 'menuId' => $menuId]);
        }
    }

    public function down()
    {
        $menus = DB::table('menu')->where('rota', 'painel.consumo_medio')->pluck('id');
        DB::table('menuUsuario')->whereIn('menuId', $menus)->delete();
        DB::table('menu')->whereIn('id', $menus)->delete();
    }
}
