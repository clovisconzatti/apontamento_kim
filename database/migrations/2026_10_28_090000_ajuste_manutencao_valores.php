<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AjusteManutencaoValores extends Migration
{
    public function up()
    {
        // double(6,2) aceitava no máximo 9999,99: horímetro (ex.: 10826) e custos maiores davam erro ao gravar
        DB::statement('ALTER TABLE manutencao
            MODIFY horimetro DOUBLE(15,2) NULL,
            MODIFY custo DOUBLE(15,2) NULL,
            MODIFY manutencao_diaria DOUBLE(15,2) NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE manutencao
            MODIFY horimetro DOUBLE(6,2) NULL,
            MODIFY custo DOUBLE(6,2) NULL,
            MODIFY manutencao_diaria DOUBLE(6,2) NULL');
    }
}
