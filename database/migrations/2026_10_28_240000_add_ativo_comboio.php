<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAtivoComboio extends Migration
{
    // Comboio ativo ou inativo ("Sim" / "Nao", como no cadastro de Equipamento); os que já existem ficam ativos
    public function up()
    {
        if (!Schema::hasColumn('comboio', 'ativo')) {
            Schema::table('comboio', function (Blueprint $table) {
                $table->string('ativo', 3)->default('Sim')->after('obs');
            });
        }
    }

    public function down()
    {
        Schema::table('comboio', function (Blueprint $table) {
            $table->dropColumn('ativo');
        });
    }
}
