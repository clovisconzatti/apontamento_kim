<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class combustivel extends Migration
{
    public function up()
    {
        Schema::create('combustivel', function(Blueprint $table){
            $table->increments('id');
            $table->date('data')->nullable();
            $table->integer('equipamento')->nullable();
            $table->integer('litros')->nullable();
            $table->integer('km')->nullable();
            $table->integer('horas')->nullable();
            $table->string('combustivel',20)->nullable();
            $table->string('obs',50)->nullable();


            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('combustivel');
    }
}
