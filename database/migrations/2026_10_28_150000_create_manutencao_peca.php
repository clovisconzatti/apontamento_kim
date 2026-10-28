<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManutencaoPeca extends Migration
{
    // Peças usadas numa manutenção: várias linhas por manutenção (mesma estrutura da revisao_peca)
    public function up()
    {
        if (Schema::hasTable('manutencao_peca')) {
            return;
        }
        Schema::create('manutencao_peca', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('manutencao_id')->index();
            $table->integer('peca')->nullable();
            $table->double('quantidade', 15, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('manutencao_peca');
    }
}
