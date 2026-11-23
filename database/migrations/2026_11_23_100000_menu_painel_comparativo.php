<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MenuPainelComparativo extends Migration
{
    // Painel de Operações · Comparativo (depois de Consumo por Produção), liberado para quem já acessa o Consumo Médio
    public function up()
    {
        if (DB::table('menu')->where('rota', 'painel.comparativo')->whereNull('deleted_at')->exists()) {
            return;
        }
        $menuId = DB::table('menu')->insertGetId([
            'ordem'     => '04.0054',
            'descricao' => 'Comparativo',
            'tipo'      => 'Link',
            'rota'      => 'painel.comparativo',
            'icone'     => 'fas fa-balance-scale',
        ]);

        $vizinho = DB::table('menu')->where('rota', 'painel.consumo_medio')->whereNull('deleted_at')->value('id');
        $usuarios = DB::table('menuUsuario')->where('menuId', $vizinho)->whereNull('deleted_at')->pluck('usuarioId');
        foreach ($usuarios as $usuarioId) {
            DB::table('menuUsuario')->insert(['usuarioId' => $usuarioId, 'menuId' => $menuId]);
        }
    }

    public function down()
    {
        $menus = DB::table('menu')->where('rota', 'painel.comparativo')->pluck('id');
        DB::table('menuUsuario')->whereIn('menuId', $menus)->delete();
        DB::table('menu')->whereIn('id', $menus)->delete();
    }
}
