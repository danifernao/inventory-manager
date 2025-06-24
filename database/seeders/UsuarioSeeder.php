<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Crea por defecto una cuenta de respaldo, una administrativa y una operativa.
     */
    public function run()
    {
        DB::table('usuarios')->insert([
            'primer_nombre' => 'Admin',
            'correo' => 'admin@example.com',
            'contrasena' => Hash::make('admin@IWAPP#22'),
            'es_administrador' => 1,
            'es_respaldo' => 1
        ]);
        
        DB::table('usuarios')->insert([
            'primer_nombre' => 'Emily',
            'primer_apellido' => 'Haines',
            'correo' => 'emily@example.com',
            'contrasena' => Hash::make('emily@IWAPP#22'),
            'es_administrador' => 1
        ]);
        
        DB::table('usuarios')->insert([
            'primer_nombre' => 'Ludovico',
            'primer_apellido' => 'Einaudi',
            'correo' => 'ludovico@example.com',
            'contrasena' => Hash::make('ludovico@IWAPP#22')
        ]);
    }
}