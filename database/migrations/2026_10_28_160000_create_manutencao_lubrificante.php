<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManutencaoLubrificante extends Migration
{
    // Lubrificantes usados numa manutenção: várias linhas por manutenção (mesma estrutura da revisao_lubrificante)
    public function up()
    {
        if (Schema::hasTable('manutencao_lubrificante')) {
            return;
        }
        Schema::create('manutencao_lubrificante', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('manutencao_id')->index();
            $table->integer('lubrificante')->nullable();
            $table->double('quantidade', 15, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('manutencao_lubrificante');
    }
}
