<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnidadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bodega_id')->nullable();
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->unsignedBigInteger('proyecto_id')->nullable();
            $table->string('numero_serie')->unique('codigo_serie');
            $table->date('fecha_adquisicion')->nullable();
            $table->decimal('valor_adquisicion', 15)->nullable()->default(0);
            $table->integer('dias_en_uso')->nullable()->default(0);
            $table->date('fecha_ingreso_proyecto')->nullable();
            $table->tinyInteger('dado_de_baja')->nullable()->default(0);
            $table->string('motivo_de_baja', 500)->nullable();
            $table->date('fecha_de_baja')->nullable();
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
        Schema::dropIfExists('unidades');
    }
}
