<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ValoresItensOs extends Migration
{
    // Importação da OS em PDF: valores das peças e os serviços executados na Manutenção e na Revisão
    public function up()
    {
        foreach (['manutencao_peca', 'revisao_peca'] as $tabela) {
            foreach (['valor_unitario' => 4, 'desconto' => 2, 'valor_total' => 2] as $coluna => $casas) {
                if (!Schema::hasColumn($tabela, $coluna)) {
                    Schema::table($tabela, function (Blueprint $table) use ($coluna, $casas) {
                        $table->double($coluna, 15, $casas)->nullable();
                    });
                }
            }
        }

        foreach (['manutencao', 'revisao'] as $pai) {
            if (!Schema::hasTable($pai.'_servico')) {
                Schema::create($pai.'_servico', function (Blueprint $table) use ($pai) {
                    $table->increments('id');
                    $table->integer($pai.'_id')->index();
                    $table->string('servico', 150);
                    $table->string('mecanico', 100)->nullable();
                    $table->date('data')->nullable();
                    $table->time('hora_inicial')->nullable();
                    $table->time('hora_final')->nullable();
                    $table->double('valor_hora', 15, 2)->nullable();
                    $table->double('valor_total', 15, 2)->nullable();

                    $table->timestamps();
                });
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('manutencao_servico');
        Schema::dropIfExists('revisao_servico');
        foreach (['manutencao_peca', 'revisao_peca'] as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropColumn(['valor_unitario', 'desconto', 'valor_total']);
            });
        }
    }
}
