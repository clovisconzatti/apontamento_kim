<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateIntervaloRevisao extends Migration
{
    // Cadastro de Intervalo de Revisão (horas entre revisões) e, na Revisão, o intervalo usado e a próxima revisão prevista.
    // Horímetro da próxima revisão passa a ser: horímetro atual + horas do intervalo.
    public function up()
    {
        if (!Schema::hasTable('intervalo_revisao')) {
            Schema::create('intervalo_revisao', function (Blueprint $table) {
                $table->increments('id');
                $table->double('horas', 15, 2)->nullable();

                $table->softDeletes();
                $table->timestamps();
            });
        }

        Schema::table('revisao', function (Blueprint $table) {
            if (!Schema::hasColumn('revisao', 'intervalo_revisao')) {
                $table->integer('intervalo_revisao')->nullable()->after('tipo_revisao');
            }
            if (!Schema::hasColumn('revisao', 'proxima_revisao')) {
                $table->integer('proxima_revisao')->nullable()->after('intervalo_revisao'); // tipo_revisao da próxima
            }
        });

        // Menu em Cadastro, logo depois de Tipo de Revisão, liberado para quem já acessa o Tipo de Revisão
        if (!DB::table('menu')->where('rota', 'intervalo_revisao.listAll')->whereNull('deleted_at')->exists()) {
            $menuId = DB::table('menu')->insertGetId([
                'ordem'     => '01.0111',
                'descricao' => 'Intervalo de Revisão',
                'tipo'      => 'Link',
                'rota'      => 'intervalo_revisao.listAll',
                'icone'     => 'fas fa-history',
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
        $menus = DB::table('menu')->where('rota', 'intervalo_revisao.listAll')->pluck('id');
        DB::table('menuUsuario')->whereIn('menuId', $menus)->delete();
        DB::table('menu')->whereIn('id', $menus)->delete();

        Schema::table('revisao', function (Blueprint $table) {
            $table->dropColumn(['intervalo_revisao', 'proxima_revisao']);
        });
        Schema::dropIfExists('intervalo_revisao');
    }
}
