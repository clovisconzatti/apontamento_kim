<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateServicoRevisao extends Migration
{
    // Cadastro de Serviços de Revisão (ex.: Troca de Óleo do Motor) e, no Tipo de Revisão,
    // a lista de serviços e de peças (com quantidade) de cada tipo
    public function up()
    {
        if (!Schema::hasTable('servico_revisao')) {
            Schema::create('servico_revisao', function (Blueprint $table) {
                $table->increments('id');
                $table->string('servico', 150)->nullable();

                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tipo_revisao_servico')) {
            Schema::create('tipo_revisao_servico', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('tipo_revisao_id')->index();
                $table->integer('servico')->nullable();

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tipo_revisao_peca')) {
            Schema::create('tipo_revisao_peca', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('tipo_revisao_id')->index();
                $table->integer('peca')->nullable();
                $table->double('quantidade', 15, 2)->nullable();

                $table->timestamps();
            });
        }

        // Menu em Veiculos, logo depois de Intervalo de Revisão, liberado para quem já acessa o Tipo de Revisão
        if (!DB::table('menu')->where('rota', 'servico_revisao.listAll')->whereNull('deleted_at')->exists()) {
            $menuId = DB::table('menu')->insertGetId([
                'ordem'     => '02.012',
                'descricao' => 'Serviços de Revisão',
                'tipo'      => 'Link',
                'rota'      => 'servico_revisao.listAll',
                'icone'     => 'fas fa-tools',
            ]);
            $vizinho = DB::table('menu')->where('rota', 'tipo_revisao.listAll')->whereNull('deleted_at')->value('id');
            $usuarios = DB::table('menuUsuario')->where('menuId', $vizinho)->whereNull('deleted_at')->pluck('usuarioId');
            foreach ($usuarios as $usuarioId) {
                DB::table('menuUsuario')->insert(['usuarioId' => $usuarioId, 'menuId' => $menuId]);
            }
        }
    }

    public function down()
    {
        $menus = DB::table('menu')->where('rota', 'servico_revisao.listAll')->pluck('id');
        DB::table('menuUsuario')->whereIn('menuId', $menus)->delete();
        DB::table('menu')->whereIn('id', $menus)->delete();

        Schema::dropIfExists('tipo_revisao_peca');
        Schema::dropIfExists('tipo_revisao_servico');
        Schema::dropIfExists('servico_revisao');
    }
}
