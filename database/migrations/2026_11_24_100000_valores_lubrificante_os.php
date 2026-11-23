<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ValoresLubrificanteOs extends Migration
{
    // Valores do óleo lubrificante (Manutenção e Revisão) e a memória da importação da OS:
    // código do produto => lubrificante do cadastro (nulo = o usuário disse que é peça)
    public function up()
    {
        foreach (['manutencao_lubrificante', 'revisao_lubrificante'] as $tabela) {
            foreach (['valor_unitario' => 4, 'desconto' => 2, 'valor_total' => 2] as $coluna => $casas) {
                if (!Schema::hasColumn($tabela, $coluna)) {
                    Schema::table($tabela, function (Blueprint $table) use ($coluna, $casas) {
                        $table->double($coluna, 15, $casas)->nullable();
                    });
                }
            }
        }

        if (!Schema::hasTable('lubrificante_os')) {
            Schema::create('lubrificante_os', function (Blueprint $table) {
                $table->increments('id');
                $table->string('codigo', 20)->unique();
                $table->integer('lubrificante')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('lubrificante_os');
        foreach (['manutencao_lubrificante', 'revisao_lubrificante'] as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropColumn(['valor_unitario', 'desconto', 'valor_total']);
            });
        }
    }
}
