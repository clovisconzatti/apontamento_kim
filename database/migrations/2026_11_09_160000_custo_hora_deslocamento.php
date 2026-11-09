<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustoHoraDeslocamento extends Migration
{
    // Revisão e Manutenção: custo por hora (R$/h, multiplicado pelas horas trabalhadas) e custo de deslocamento (R$).
    // Na Revisão, o "custo diário" vira "custo hora". Na Manutenção, a coluna antiga "manutencao_diaria" é mantida
    // como está: os valores já lançados são totais, não valor por hora.
    public function up()
    {
        Schema::table('revisao', function (Blueprint $table) {
            if (!Schema::hasColumn('revisao', 'custo_hora')) {
                $table->double('custo_hora', 15, 2)->nullable()->after('custo_terceiro');
            }
            if (!Schema::hasColumn('revisao', 'custo_deslocamento')) {
                $table->double('custo_deslocamento', 15, 2)->nullable()->after('custo_hora');
            }
        });
        if (Schema::hasColumn('revisao', 'custo_diario')) {
            DB::table('revisao')->whereNull('custo_hora')->whereNotNull('custo_diario')->update(['custo_hora' => DB::raw('custo_diario')]);
            Schema::table('revisao', function (Blueprint $table) {
                $table->dropColumn('custo_diario');
            });
        }

        Schema::table('manutencao', function (Blueprint $table) {
            if (!Schema::hasColumn('manutencao', 'custo_hora')) {
                $table->double('custo_hora', 15, 2)->nullable()->after('manutencao_diaria');
            }
            if (!Schema::hasColumn('manutencao', 'custo_deslocamento')) {
                $table->double('custo_deslocamento', 15, 2)->nullable()->after('custo_hora');
            }
        });
    }

    public function down()
    {
        Schema::table('revisao', function (Blueprint $table) {
            $table->double('custo_diario', 15, 2)->nullable()->after('custo_terceiro');
        });
        DB::table('revisao')->update(['custo_diario' => DB::raw('custo_hora')]);
        Schema::table('revisao', function (Blueprint $table) {
            $table->dropColumn(['custo_hora', 'custo_deslocamento']);
        });
        Schema::table('manutencao', function (Blueprint $table) {
            $table->dropColumn(['custo_hora', 'custo_deslocamento']);
        });
    }
}
