<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MenuRevisaoParaVeiculos extends Migration
{
    // Tipo de Revisão e Intervalo de Revisão saem de Cadastro e vão para o fim de Veiculos
    const NOVA = ['tipo_revisao.listAll' => '02.010', 'intervalo_revisao.listAll' => '02.011'];
    const ANTIGA = ['tipo_revisao.listAll' => '01.011', 'intervalo_revisao.listAll' => '01.0111'];

    public function up()
    {
        $this->mover(self::NOVA);

        // O menu agrupa pelo título anterior na ordem: quem vê esses itens precisa ter o título "Veiculos"
        $titulo = DB::table('menu')->where('tipo', 'Título')->where('ordem', '02.000')->whereNull('deleted_at')->value('id');
        if (!$titulo) {
            return;
        }
        $itens = DB::table('menu')->whereIn('rota', array_keys(self::NOVA))->whereNull('deleted_at')->pluck('id');
        $usuarios = DB::table('menuUsuario')->whereIn('menuId', $itens)->whereNull('deleted_at')->distinct()->pluck('usuarioId');
        $comTitulo = DB::table('menuUsuario')->where('menuId', $titulo)->whereNull('deleted_at')->pluck('usuarioId')->all();
        foreach ($usuarios->diff($comTitulo) as $usuarioId) {
            DB::table('menuUsuario')->insert(['usuarioId' => $usuarioId, 'menuId' => $titulo]);
        }
    }

    public function down()
    {
        $this->mover(self::ANTIGA);
    }

    private function mover(array $ordens)
    {
        foreach ($ordens as $rota => $ordem) {
            DB::table('menu')->where('rota', $rota)->whereNull('deleted_at')->update(['ordem' => $ordem]);
        }
    }
}
