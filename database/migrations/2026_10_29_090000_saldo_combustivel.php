<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SaldoCombustivel extends Migration
{
    public function up()
    {
        // Comboio: tipo (tanque próprio entra no saldo; posto externo não) e o saldo inicial medido numa data
        Schema::table('comboio', function (Blueprint $table) {
            if (!Schema::hasColumn('comboio', 'tipo')) {
                $table->string('tipo', 10)->default('proprio')->after('ativo'); // proprio | posto
            }
            if (!Schema::hasColumn('comboio', 'saldo_inicial')) {
                $table->double('saldo_inicial', 15, 2)->nullable()->after('tipo');
            }
            if (!Schema::hasColumn('comboio', 'data_saldo_inicial')) {
                $table->date('data_saldo_inicial')->nullable()->after('saldo_inicial');
            }
        });
        // Ponto de partida: os cadastros com "POSTO" no nome são postos externos (conferir no cadastro)
        DB::table('comboio')->where('tanque', 'like', 'POSTO%')->update(['tipo' => 'posto']);

        // Menu: Painel de Operações · Saldo de Combustível (antes de Consumo Combustível)
        if (!DB::table('menu')->where('rota', 'painel.saldo_combustivel')->whereNull('deleted_at')->exists()) {
            $menuId = DB::table('menu')->insertGetId([
                'ordem'     => '04.0045',
                'descricao' => 'Saldo de Combustível',
                'tipo'      => 'Link',
                'rota'      => 'painel.saldo_combustivel',
                'icone'     => 'fas fa-battery-three-quarters',
            ]);
            $vizinho = DB::table('menu')->where('rota', 'painel.combustivel')->whereNull('deleted_at')->value('id');
            foreach (DB::table('menuUsuario')->where('menuId', $vizinho)->whereNull('deleted_at')->pluck('usuarioId') as $usuarioId) {
                DB::table('menuUsuario')->insert(['usuarioId' => $usuarioId, 'menuId' => $menuId]);
            }
        }
    }

    public function down()
    {
        $menus = DB::table('menu')->where('rota', 'painel.saldo_combustivel')->pluck('id');
        DB::table('menuUsuario')->whereIn('menuId', $menus)->delete();
        DB::table('menu')->whereIn('id', $menus)->delete();
        Schema::table('comboio', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'saldo_inicial', 'data_saldo_inicial']);
        });
    }
}
