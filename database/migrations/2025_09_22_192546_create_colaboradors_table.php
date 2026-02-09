<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColaboradorsTable extends Migration
{
    public function up()
    {
        Schema::create('colaborador', function(Blueprint $table){
            $table->increments('id');
            $table->string('colaborador',50)->nullable();
            $table->string('uf',2)->nullable();
            $table->string('ativo',3)->nullable();
            $table->integer('cod')->nullable();
            $table->string('empresa',20)->nullable();



            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('colaborador');
    }
}
