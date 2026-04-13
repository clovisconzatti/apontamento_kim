<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInformacaosTable extends Migration
{
    public function up()
    {
        Schema::create('informacao', function(Blueprint $table){
            $table->increments('id');
            $table->date('data')->nullable();
            $table->integer('fazenda')->nullable();
            $table->integer('equipamento')->nullable();
            $table->integer('atividade')->nullable();
            $table->integer('colaborador')->nullable();
            $table->time('hora_inicial')->nullable();
            $table->time('hora_final')->nullable();
            $table->double('horimetro_inicial')->nullable();
            $table->double('horimetro_final')->nullable();
            $table->integer('corte')->nullable();
            $table->double('fat_m')->nullable();
            $table->integer('origem_abastecimento')->nullable();
            $table->integer('nr_nf')->nullable();
            $table->double('qnt_diesel')->nullable();
            $table->double('horimetro_abastecimento')->nullable();
            $table->double('relogio_tanque_inicial')->nullable();
            $table->double('relogio_tanque_final')->nullable();
            $table->double('qnt_lubrificante')->nullable();
            $table->integer('tipo_lubrificante')->nullable();
            $table->integer('producao_terceiros')->nullable();
            $table->double( 'comprimento_madeira')->nullable();
            $table->integer('baldeio_curto')->nullable();
            $table->integer('baldeio_medio')->nullable();
            $table->integer('baldeio_longo')->nullable();
            $table->integer('arrasto_curto')->nullable();
            $table->integer('arrasto_medio')->nullable();
            $table->integer('arrasto_longo')->nullable();
            $table->string('obs',255)->nullable();
            $table->integer('clima')->nullable();
            $table->integer('terreno')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('informacao');
    }
}
