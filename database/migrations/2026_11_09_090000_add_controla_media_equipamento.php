<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddControlaMediaEquipamento extends Migration
{
    // Equipamento entra ou não no controle de média de consumo ("Sim" / "Nao", como o Ativo); os que já existem ficam "Sim"
    public function up()
    {
        if (!Schema::hasColumn('equipamento', 'controla_media')) {
            Schema::table('equipamento', function (Blueprint $table) {
                $table->string('controla_media', 3)->default('Sim')->after('consumo_maximo');
            });
        }
    }

    public function down()
    {
        Schema::table('equipamento', function (Blueprint $table) {
            $table->dropColumn('controla_media');
        });
    }
}
