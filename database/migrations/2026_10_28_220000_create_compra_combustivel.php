<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCompraCombustivel extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('compra_combustivel')) {
            Schema::create('compra_combustivel', function (Blueprint $table) {
                $table->increments('id');
                $table->date('data')->nullable();
                $table->integer('fornecedor')->nullable();
                $table->string('nr_nf', 30)->nullable();
                $table->integer('combustivel')->nullable(); // tipo_combustivel
                $table->integer('tanque')->nullable();      // comboio de destino
                $table->double('litros', 15, 2)->nullable();
                $table->double('valor_litro', 15, 4)->nullable(); // custo por litro = valor total ÷ litros (calculado ao gravar)
                $table->double('valor_total', 15, 2)->nullable(); // valor total da NF

                $table->softDeletes();
                $table->timestamps();
                $table->index(['data']);
                $table->index(['fornecedor', 'nr_nf']);
            });
        }

        // Menu: Compra de Combustível em Apontamento, liberado para quem já acessa a Manutenção
        if (!DB::table('menu')->where('rota', 'compra_combustivel.listAll')->whereNull('deleted_at')->exists()) {
            $menuId = DB::table('menu')->insertGetId([
                'ordem'     => '03.005',
                'descricao' => 'Compra de Combustível',
                'tipo'      => 'Link',
                'rota'      => 'compra_combustivel.listAll',
                'icone'     => 'fas fa-file-invoice-dollar',
            ]);
            $vizinho = DB::table('menu')->where('rota', 'manutencao.listAll')->whereNull('deleted_at')->value('id');
            foreach (DB::table('menuUsuario')->where('menuId', $vizinho)->whereNull('deleted_at')->pluck('usuarioId') as $usuarioId) {
                DB::table('menuUsuario')->insert(['usuarioId' => $usuarioId, 'menuId' => $menuId]);
            }
        }
    }

    public function down()
    {
        $menus = DB::table('menu')->where('rota', 'compra_combustivel.listAll')->pluck('id');
        DB::table('menuUsuario')->whereIn('menuId', $menus)->delete();
        DB::table('menu')->whereIn('id', $menus)->delete();
        Schema::dropIfExists('compra_combustivel');
    }
}
