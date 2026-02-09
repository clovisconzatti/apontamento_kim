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
            $table->integer('horimetro_inicial')->nullable();
            $table->integer('horimetro_final')->nullable();
            $table->integer('corte')->nullable();
            $table->integer('fat_m')->nullable();
            $table->integer('origem_abastecimento')->nullable();
            $table->integer('nr_nf')->nullable();
            $table->integer('qnt_diesel')->nullable();
            $table->integer('horimetro_abastecimento')->nullable();
            $table->integer('relogio_tanque_inicial')->nullable();
            $table->integer('relogio_tanque_final')->nullable();
            $table->integer('qnt_lubrificante')->nullable();
            $table->integer('tipo_lubrificante')->nullable();
            $table->integer('carregamento')->nullable();
            $table->integer('veiculo_carregado')->nullable();
            $table->integer('descarregamento')->nullable();
            $table->integer('veiculo_descarregado')->nullable();
            $table->integer('producao_terceiros')->nullable();
            $table->integer('comprimento_madeira')->nullable();
            $table->integer('baldeio_curto')->nullable();
            $table->integer('baldeio_medio')->nullable();
            $table->integer('baldeio_longo')->nullable();
            $table->integer('arrasto_curto')->nullable();
            $table->integer('arrasto_medio')->nullable();
            $table->integer('arrasto_longo')->nullable();
            $table->string('obs',244)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('informacao');
    }
}
