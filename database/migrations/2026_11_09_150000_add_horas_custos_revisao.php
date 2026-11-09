<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHorasCustosRevisao extends Migration
{
    // Revisão: hora de início e de fim da revisão, custo de terceiro e custo diário (R$).
    // Tipo de Revisão: lista de óleos lubrificantes (com quantidade), como a de peças.
    public function up()
    {
        if (!Schema::hasTable('tipo_revisao_lubrificante')) {
            Schema::create('tipo_revisao_lubrificante', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('tipo_revisao_id')->index();
                $table->integer('lubrificante')->nullable();
                $table->double('quantidade', 15, 2)->nullable();

                $table->timestamps();
            });
        }

        Schema::table('revisao', function (Blueprint $table) {
            if (!Schema::hasColumn('revisao', 'hora_inicial')) {
                $table->time('hora_inicial')->nullable()->after('nr_os');
            }
            if (!Schema::hasColumn('revisao', 'hora_final')) {
                $table->time('hora_final')->nullable()->after('hora_inicial');
            }
            if (!Schema::hasColumn('revisao', 'custo_terceiro')) {
                $table->double('custo_terceiro', 15, 2)->nullable()->after('hora_final');
            }
            if (!Schema::hasColumn('revisao', 'custo_diario')) {
                $table->double('custo_diario', 15, 2)->nullable()->after('custo_terceiro');
            }
        });
    }

    public function down()
    {
        Schema::table('revisao', function (Blueprint $table) {
            $table->dropColumn(['hora_inicial', 'hora_final', 'custo_terceiro', 'custo_diario']);
        });
        Schema::dropIfExists('tipo_revisao_lubrificante');
    }
}
