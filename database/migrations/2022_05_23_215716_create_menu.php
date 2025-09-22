<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenu extends Migration
{
    public function up()
    {
        Schema::create('menu', function(Blueprint $table){
            $table->increments('id');
            $table->string('ordem',20)->nullable();
            $table->string('descricao',100)->nullable();
            $table->string('tipo',100)->nullable();
            $table->string('rota',100)->nullable();
            $table->string('icone',100)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu');
    }
}
