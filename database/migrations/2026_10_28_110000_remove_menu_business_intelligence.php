<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveMenuBusinessIntelligence extends Migration
{
    // A seção "Business Intelligence" apontava para a rota bi.index, que nunca foi criada:
    // o título aparecia sozinho no menu. Exclusão lógica para reaproveitar quando os dashboards existirem.
    public function up()
    {
        DB::table('menu')
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->where(function ($t) {
                    $t->where('tipo', 'Título')->where('descricao', 'Business Intelligence');
                })->orWhere('rota', 'bi.index');
            })
            ->update(['deleted_at' => now()]);
    }

    public function down()
    {
        DB::table('menu')
            ->where(function ($q) {
                $q->where(function ($t) {
                    $t->where('tipo', 'Título')->where('descricao', 'Business Intelligence');
                })->orWhere('rota', 'bi.index');
            })
            ->update(['deleted_at' => null]);
    }
}
