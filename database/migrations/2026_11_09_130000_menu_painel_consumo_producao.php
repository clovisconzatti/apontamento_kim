<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MenuPainelConsumoProducao extends Migration
{
    // Painel de Operações · Consumo por Produção (logo depois de Consumo Fora da Faixa), liberado para quem já acessa o Consumo Médio
    public function up()
    {
        if (DB::table('menu')->where('rota', 'painel.consumo_producao')->whereNull('deleted_at')->exists()) {
            return;
        }
        $menuId = DB::table('menu')->insertGetId([
            'ordem'     => '04.0053',
            'descricao' => 'Consumo por Produção',
            'tipo'      => 'Link',
            'rota'      => 'painel.consumo_producao',
            'icone'     => 'fas fa-tree',
        ]);

        $vizinho = DB::table('menu')->where('rota', 'painel.consumo_medio')->whereNull('deleted_at')->value('id');
        $usuarios = DB::table('menuUsuario')->where('menuId', $vizinho)->whereNull('deleted_at')->pluck('usuarioId');
        foreach ($usuarios as $usuarioId) {
            DB::table('menuUsuario')->insert(['usuarioId' => $usuarioId, 'menuId' => $menuId]);
        }
    }

    public function down()
    {
        $menus = DB::table('menu')->where('rota', 'painel.consumo_producao')->pluck('id');
        DB::table('menuUsuario')->whereIn('menuId', $menus)->delete();
        DB::table('menu')->whereIn('id', $menus)->delete();
    }
}
