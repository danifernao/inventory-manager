<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AplicacionSeeder extends Seeder
{
    /**
     * Registra los datos básico de la aplicación.
     */
    public function run()
    {
        DB::table('aplicacion')->insertOrIgnore([
            'titulo' => 'Gestión de Inventarios',
            'descripcion' => 'Aplicación web para la gestión de inventarios.',
            'img_icono_url' => '/storage/aplicacion/icono.png',
            'img_favicon_url' => '/storage/aplicacion/favicon.png',
            'img_autenticacion_url' => '/storage/aplicacion/cabecera-autenticacion.png',
            'img_inventario_url' => '/storage/aplicacion/cabecera-inventario.png'
        ]);
    }
}
