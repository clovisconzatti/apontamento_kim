<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComboiosTable extends Migration
{
    public function up()
    {
        Schema::create('comboio', function(Blueprint $table){
            $table->increments('id');
            $table->string('tanque',30)->nullable();
            $table->integer('capacidade')->nullable();
            $table->integer('fazenda')->nullable();
            $table->string('uf',2)->nullable();
            $table->string('obs',100)->nullable();


            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('comboio');
    }
}
