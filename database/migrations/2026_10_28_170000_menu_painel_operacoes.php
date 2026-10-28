<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MenuPainelOperacoes extends Migration
{
    // Seção "Painel de Operações": relatórios, gráficos e painéis de acompanhamento.
    // Nasce com o Consumo por Máquina (uma seção sem link aparece vazia no menu).
    // Liberada para quem já acessa a Informação Diária.
    public function up()
    {
        $usuarios = DB::table('menuUsuario')
                        ->where('menuId', DB::table('menu')->where('rota', 'informacao.listAll')->whereNull('deleted_at')->value('id'))
                        ->whereNull('deleted_at')
                        ->pluck('usuarioId');

        $this->criarMenu('04.000', 'Painel de Operações', 'Título', null, null, $usuarios);
        $this->criarMenu('04.001', 'Consumo por Máquina', 'Link', 'painel.consumo_maquina', 'fas fa-chart-bar', $usuarios);
    }

    public function down()
    {
        $menus = DB::table('menu')
                    ->where(function ($q) {
                        $q->where('tipo', 'Título')->where('descricao', 'Painel de Operações');
                    })
                    ->orWhere('rota', 'painel.consumo_maquina')
                    ->pluck('id');
        DB::table('menuUsuario')->whereIn('menuId', $menus)->delete();
        DB::table('menu')->whereIn('id', $menus)->delete();
    }

    private function criarMenu($ordem, $descricao, $tipo, $rota, $icone, $usuarios)
    {
        $existe = DB::table('menu')->whereNull('deleted_at')->where('descricao', $descricao)->where('tipo', $tipo)->exists();
        if ($existe) {
            return;
        }
        $menuId = DB::table('menu')->insertGetId([
            'ordem'     => $ordem,
            'descricao' => $descricao,
            'tipo'      => $tipo,
            'rota'      => $rota,
            'icone'     => $icone,
        ]);
        foreach ($usuarios as $usuarioId) {
            DB::table('menuUsuario')->insert(['usuarioId' => $usuarioId, 'menuId' => $menuId]);
        }
    }
}
