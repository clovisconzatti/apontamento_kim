<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAjusteCombustivel extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ajuste_combustivel')) {
            Schema::create('ajuste_combustivel', function (Blueprint $table) {
                $table->increments('id');
                $table->date('data')->nullable();
                $table->integer('tanque')->nullable();          // comboio ajustado
                $table->double('quantidade', 15, 2)->nullable(); // litros, sempre positivo
                $table->string('tipo', 10)->nullable();          // falta | sobra

                $table->softDeletes();
                $table->timestamps();
                $table->index(['tanque', 'data']);
            });
        }

        // Menu: Ajuste de Saldo em Apontamento, liberado para quem já acessa a Manutenção
        if (!DB::table('menu')->where('rota', 'ajuste_combustivel.listAll')->whereNull('deleted_at')->exists()) {
            $menuId = DB::table('menu')->insertGetId([
                'ordem'     => '03.006',
                'descricao' => 'Ajuste de Saldo',
                'tipo'      => 'Link',
                'rota'      => 'ajuste_combustivel.listAll',
                'icone'     => 'fas fa-balance-scale',
            ]);
            $vizinho = DB::table('menu')->where('rota', 'manutencao.listAll')->whereNull('deleted_at')->value('id');
            foreach (DB::table('menuUsuario')->where('menuId', $vizinho)->whereNull('deleted_at')->pluck('usuarioId') as $usuarioId) {
                DB::table('menuUsuario')->insert(['usuarioId' => $usuarioId, 'menuId' => $menuId]);
            }
        }
    }

    public function down()
    {
        $menus = DB::table('menu')->where('rota', 'ajuste_combustivel.listAll')->pluck('id');
        DB::table('menuUsuario')->whereIn('menuId', $menus)->delete();
        DB::table('menu')->whereIn('id', $menus)->delete();
        Schema::dropIfExists('ajuste_combustivel');
    }
}
