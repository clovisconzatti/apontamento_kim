<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEquipamento extends Migration
{

    public function up()
    {
       Schema::table('equipamento', function (Blueprint $table) {
        $table->integer('ano')->nullable();
        $table->string('ativo',3)->nullable();
        $table->string('uf',2)->nullable();
        $table->date('data_partida')->nullable();
        $table->integer('tipo')->nullable();
        $table->integer('atividade')->nullable();
        $table->integer('cilindros')->nullable();
        $table->integer('operacao')->nullable();
        $table->double('consumo_minimo')->nullable();
        $table->double('consumo_maximo')->nullable();

        });
    }


}

