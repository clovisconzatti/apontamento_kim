<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFazendasTable extends Migration
{
    public function up()
    {
        Schema::create('fazenda', function(Blueprint $table){
            $table->increments('id');
            $table->string('fazenda',50)->nullable();
            $table->string('uf',2)->nullable();
            $table->string('apontador',20)->nullable();
            $table->string('ativa',3)->nullable();


            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fazenda');
    }
}
