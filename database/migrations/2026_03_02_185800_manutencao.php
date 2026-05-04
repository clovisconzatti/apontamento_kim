<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Manutencao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manutencao', function(Blueprint $table){
            $table->increments('id');
            $table->date('data')->nullable();
            $table->integer('ord_servico')->nullable();
            $table->integer('fazenda')->nullable();
            $table->integer('equipamento')->nullable();
            $table->integer('operador')->nullable();
            $table->time('hora_inicial')->nullable();
            $table->time('hora_final')->nullable();
            $table->double('horimetro')->nullable();
            $table->integer('tipo_manutencao')->nullable();
            $table->double('custo')->nullable();
            $table->double('manutencao_diaria')->nullable();
            $table->integer('situacao')->nullable();
            $table->string('obs',255)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('manutencao');
    }
}
