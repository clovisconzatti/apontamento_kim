<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRevisao extends Migration
{
    public function up()
    {
        // Tabelas podem já existir (banco restaurado de backup sem o registro desta migration)
        if (!Schema::hasTable('tipo_revisao'))
        Schema::create('tipo_revisao', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descricao', 100)->nullable();
            $table->double('intervalo', 15, 2)->nullable(); // KM/Horas até a próxima revisão
            $table->string('obs', 255)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        if (!Schema::hasTable('revisao'))
        Schema::create('revisao', function (Blueprint $table) {
            $table->increments('id');
            $table->date('data')->nullable();
            $table->integer('equipamento')->nullable();
            $table->double('horimetro_atual', 15, 2)->nullable();
            $table->integer('tipo_revisao')->nullable();
            $table->string('nr_os', 30)->nullable();
            $table->double('horimetro_proxima', 15, 2)->nullable(); // horímetro atual + intervalo do tipo

            $table->softDeletes();
            $table->timestamps();
            $table->index(['equipamento', 'data']);
        });

        // Menus: Tipo de Revisão em Cadastro e Revisão em Apontamento,
        // liberados para quem já acessa a tela vizinha
        $this->criarMenu('01.011', 'Tipo de Revisão', 'tipo_revisao.listAll', 'fas fa-clipboard-check', 'terreno.listAll');
        $this->criarMenu('03.004', 'Revisão', 'revisao.listAll', 'fas fa-calendar-check', 'manutencao.listAll');
    }

    public function down()
    {
        $menus = DB::table('menu')->whereIn('rota', ['tipo_revisao.listAll', 'revisao.listAll'])->pluck('id');
        DB::table('menuUsuario')->whereIn('menuId', $menus)->delete();
        DB::table('menu')->whereIn('id', $menus)->delete();

        Schema::dropIfExists('revisao');
        Schema::dropIfExists('tipo_revisao');
    }

    private function criarMenu($ordem, $descricao, $rota, $icone, $rotaVizinha)
    {
        if (DB::table('menu')->where('rota', $rota)->whereNull('deleted_at')->exists()) {
            return;
        }
        $menuId = DB::table('menu')->insertGetId([
            'ordem'     => $ordem,
            'descricao' => $descricao,
            'tipo'      => 'Link',
            'rota'      => $rota,
            'icone'     => $icone,
        ]);

        $vizinho = DB::table('menu')->where('rota', $rotaVizinha)->whereNull('deleted_at')->value('id');
        $usuarios = DB::table('menuUsuario')->where('menuId', $vizinho)->whereNull('deleted_at')->pluck('usuarioId');
        foreach ($usuarios as $usuarioId) {
            DB::table('menuUsuario')->insert(['usuarioId' => $usuarioId, 'menuId' => $menuId]);
        }
    }
}
