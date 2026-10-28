<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevisaoItens extends Migration
{
    // Peças e lubrificantes usados numa revisão: várias linhas por revisão
    public function up()
    {
        // Tabelas podem já existir (banco restaurado de backup sem o registro desta migration)
        if (!Schema::hasTable('revisao_peca'))
        Schema::create('revisao_peca', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('revisao_id')->index();
            $table->integer('peca')->nullable();
            $table->double('quantidade', 15, 2)->nullable();

            $table->timestamps();
        });

        if (!Schema::hasTable('revisao_lubrificante'))
        Schema::create('revisao_lubrificante', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('revisao_id')->index();
            $table->integer('lubrificante')->nullable();
            $table->double('quantidade', 15, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('revisao_lubrificante');
        Schema::dropIfExists('revisao_peca');
    }
}
