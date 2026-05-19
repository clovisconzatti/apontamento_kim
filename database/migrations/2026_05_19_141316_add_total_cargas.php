<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalCargas extends Migration
{
    public function up()
    {
       Schema::table('informacao', function (Blueprint $table) {
        $table->double('carregamento', 15, 2)->nullable();

        });
    }


}
