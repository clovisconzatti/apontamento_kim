<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MenuPainelHorasConsumo extends Migration
{
    // Novos painéis do Painel de Operações, liberados para quem já acessa o Painel de Operações
    const MENUS = [
        ['04.003', 'Horas Homem',          'painel.horas_homem',   'fas fa-user-clock'],
        ['04.004', 'Horas Máquina',        'painel.horas_maquina', 'fas fa-tractor'],
        ['04.005', 'Consumo Combustível',  'painel.combustivel',   'fas fa-gas-pump'],
        ['04.006', 'Consumo Lubrificante', 'painel.lubrificante',  'fas fa-oil-can'],
    ];

    public function up()
    {
        $vizinho = DB::table('menu')->where('rota', 'painel.consumo_maquina')->whereNull('deleted_at')->value('id');
        $usuarios = DB::table('menuUsuario')->where('menuId', $vizinho)->whereNull('deleted_at')->pluck('usuarioId');

        foreach (self::MENUS as [$ordem, $descricao, $rota, $icone]) {
            if (DB::table('menu')->where('rota', $rota)->whereNull('deleted_at')->exists()) {
                continue;
            }
            $menuId = DB::table('menu')->insertGetId([
                'ordem' => $ordem, 'descricao' => $descricao, 'tipo' => 'Link', 'rota' => $rota, 'icone' => $icone,
            ]);
            foreach ($usuarios as $usuarioId) {
                DB::table('menuUsuario')->insert(['usuarioId' => $usuarioId, 'menuId' => $menuId]);
            }
        }
    }

    public function down()
    {
        $menus = DB::table('menu')->whereIn('rota', array_column(self::MENUS, 2))->pluck('id');
        DB::table('menuUsuario')->whereIn('menuId', $menus)->delete();
        DB::table('menu')->whereIn('id', $menus)->delete();
    }
}
