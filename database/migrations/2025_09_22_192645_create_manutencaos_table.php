<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManutencaosTable extends Migration
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
            $table->integer('maquina')->nullable();
            $table->integer('operador')->nullable();
            $table->time('hora_inicial')->nullable();
            $table->time('hora_final')->nullable();
            $table->integer('horimetro')->nullable();
            $table->integer('tipo_manutencao')->nullable();
            $table->integer('custo')->nullable();
            $table->string('manutencao_diaria',50)->nullable();
            $table->integer('situacao')->nullable();


            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('manutencao');
    }
}
