<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddObsRevisao extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('revisao', 'obs')) {
            return;
        }
        Schema::table('revisao', function (Blueprint $table) {
            $table->string('obs', 255)->nullable()->after('horimetro_proxima');
        });
    }

    public function down()
    {
        Schema::table('revisao', function (Blueprint $table) {
            $table->dropColumn('obs');
        });
    }
}
