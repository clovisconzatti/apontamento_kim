<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AjusteApontamentoAnexoIndice extends Migration
{
    public function up()
    {
        // A coluna "anexo" é usada pelo upload, mas nunca foi criada por migration
        if (!Schema::hasColumn('apontamento', 'anexo')) {
            Schema::table('apontamento', function (Blueprint $table) {
                $table->string('anexo', 150)->nullable();
            });
        }

        // Índice para a busca do registro anterior (último km / última hora) por equipamento
        $existe = DB::select("SHOW INDEX FROM apontamento WHERE Key_name = 'apontamento_equipamento_data_index'");
        if (!$existe) {
            Schema::table('apontamento', function (Blueprint $table) {
                $table->index(['equipamento', 'data'], 'apontamento_equipamento_data_index');
            });
        }
    }

    public function down()
    {
        Schema::table('apontamento', function (Blueprint $table) {
            $table->dropIndex('apontamento_equipamento_data_index');
        });
    }
}
