<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('primer_nombre');
            $table->string('segundo_nombre', 50)->nullable();
            $table->string('primer_apellido')->nullable();
            $table->string('segundo_apellido')->nullable();
            $table->string('tipo_identificacion')->nullable();
            $table->string('numero_identificacion')->nullable();
            $table->char('genero', 4)->nullable()->default('u');
            $table->string('correo')->unique();
            $table->string('contrasena')->nullable();
            $table->rememberToken();
            $table->string('foto_perfil_url')->nullable();
            $table->tinyInteger('es_administrador')->nullable()->default(0);
            $table->tinyInteger('es_respaldo')->nullable()->default(0);
            $table->tinyInteger('esta_habilitado')->nullable()->default(1);
            $table->timestamp('fecha_inhabilitacion')->nullable();
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
        Schema::dropIfExists('usuarios');
    }
}
