<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuUsuario extends Migration
{
    public function up()
    {
        Schema::create('menuUsuario', function(Blueprint $table){
            $table->increments('id');
            $table->integer('usuarioId');
            $table->integer('menuId');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('menuUsuario');
    }
}
