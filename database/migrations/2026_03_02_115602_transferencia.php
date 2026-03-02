<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Transferencia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transferencia', function(Blueprint $table){
           $table->increments('id');
            $table->date('data')->nullable();
            $table->integer('origem')->nullable();
            $table->integer('destino')->nullable();
            $table->integer('litros')->nullable();
            $table->integer('nr_doc')->nullable();
            $table->integer('combustivel')->nullable();
            $table->integer('horimetro')->nullable();
            $table->integer('operador')->nullable();
            $table->integer('fazenda')->nullable();
            $table->integer('horimetro_inicial')->nullable();
            $table->integer('horimetro_final')->nullable();
            $table->integer('tanque')->nullable();
            $table->string('obs',100)->nullable();


            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transferencia');
    }
}
