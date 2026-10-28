<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalDescargas extends Migration
{
    public function up()
    {
        Schema::table('informacao', function (Blueprint $table) {
            $table->double('descarg_julieta', 15, 2)->nullable();
            $table->double('descarg_truck', 15, 2)->nullable();
            $table->double('descarg_bitrem', 15, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('informacao', function (Blueprint $table) {
            $table->dropColumn(['descarg_julieta', 'descarg_truck', 'descarg_bitrem']);
        });
    }
}
