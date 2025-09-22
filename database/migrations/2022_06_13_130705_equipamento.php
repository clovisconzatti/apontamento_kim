<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Equipamento extends Migration
{
    public function up()
    {
        Schema::create('equipamento', function(Blueprint $table){
            $table->increments('id');
            $table->string('placa',20)->nullable();
            $table->string('equipamento',50)->nullable();


            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('equipamento');
    }
}
