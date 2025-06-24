<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBodegaProductoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bodega_producto', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bodega_id');
            $table->unsignedBigInteger('producto_id');
            $table->integer('unidades_minimas')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bodega_producto');
    }
}
