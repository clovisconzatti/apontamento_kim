<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Alteracamposapontamento extends Migration
{

    public function up()
    {
        Schema::table('apontamento', function (Blueprint $table) {
            $table->float('litros')->nullable()->change();
            $table->float('km')->nullable()->change();
            $table->float('horas')->nullable()->change();

        });
    }

}
