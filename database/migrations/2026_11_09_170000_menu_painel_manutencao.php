<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MenuPainelManutencao extends Migration
{
    // Painel de Operações · Manutenções (logo depois de Revisões), liberado para quem já acessa o painel de Revisões
    public function up()
    {
        if (DB::table('menu')->where('rota', 'painel.manutencao')->whereNull('deleted_at')->exists()) {
            return;
        }
        $menuId = DB::table('menu')->insertGetId([
            'ordem'     => '04.0021',
            'descricao' => 'Manutenções',
            'tipo'      => 'Link',
            'rota'      => 'painel.manutencao',
            'icone'     => 'fas fa-wrench',
        ]);

        $vizinho = DB::table('menu')->where('rota', 'painel.revisoes')->whereNull('deleted_at')->value('id');
        $usuarios = DB::table('menuUsuario')->where('menuId', $vizinho)->whereNull('deleted_at')->pluck('usuarioId');
        foreach ($usuarios as $usuarioId) {
            DB::table('menuUsuario')->insert(['usuarioId' => $usuarioId, 'menuId' => $menuId]);
        }
    }

    public function down()
    {
        $menus = DB::table('menu')->where('rota', 'painel.manutencao')->pluck('id');
        DB::table('menuUsuario')->whereIn('menuId', $menus)->delete();
        DB::table('menu')->whereIn('id', $menus)->delete();
    }
}
