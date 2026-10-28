<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MenuPainelRevisoes extends Migration
{
    // Painel de Operações · Revisões, liberado para quem já acessa o Painel de Operações
    public function up()
    {
        if (DB::table('menu')->where('rota', 'painel.revisoes')->whereNull('deleted_at')->exists()) {
            return;
        }
        $menuId = DB::table('menu')->insertGetId([
            'ordem'     => '04.002',
            'descricao' => 'Revisões',
            'tipo'      => 'Link',
            'rota'      => 'painel.revisoes',
            'icone'     => 'fas fa-calendar-check',
        ]);

        $vizinho = DB::table('menu')->where('rota', 'painel.consumo_maquina')->whereNull('deleted_at')->value('id');
        $usuarios = DB::table('menuUsuario')->where('menuId', $vizinho)->whereNull('deleted_at')->pluck('usuarioId');
        foreach ($usuarios as $usuarioId) {
            DB::table('menuUsuario')->insert(['usuarioId' => $usuarioId, 'menuId' => $menuId]);
        }
    }

    public function down()
    {
        $menus = DB::table('menu')->where('rota', 'painel.revisoes')->pluck('id');
        DB::table('menuUsuario')->whereIn('menuId', $menus)->delete();
        DB::table('menu')->whereIn('id', $menus)->delete();
    }
}
