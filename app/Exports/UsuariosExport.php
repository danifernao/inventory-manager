<?php

namespace App\Exports;

use App\Models\Usuario;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsuariosExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStrictNullComparison, WithStyles
{
    public function collection()
    {
        return Usuario::where('es_respaldo', 0)->get();
    }
    
    public function map($usuario): array
    {
        $genero = array(
            'h' => 'Hombre',
            'm' => 'Mujer',
            'n' => 'No binario',
            'u' => ''
        );
        
        $tipo_identificacion = array(
            'cc' => 'Cédula de ciudadanía',
            'ce' => 'Cédula de extranjería',
            'ti' => 'Tarjeta de identidad',
            'rc' => 'Registro civil',
            'di' => 'Otro'
        );
        
        return [
            $usuario->primer_nombre,
            $usuario->segundo_nombre,
            $usuario->primer_apellido,
            $usuario->segundo_apellido,
            $usuario->tipo_identificacion,
            $usuario->numero_identificacion,
            $genero[$usuario->genero ?: ''],
            $usuario->correo,
            $usuario->created_at ? Date::dateTimeToExcel($usuario->created_at) : '',
            $usuario->es_administrador ? 'Administrador' : 'Operador',
            $usuario->esta_habilitado ? 'Habilitado' : 'No habilitado'
        ];
    }
    
    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_DATE_DDMMYYYY
        ];
    }
    
    public function headings(): array
    {
        return ['Primer nombre', 'Segundo nombre', 'Primer apellido', 'Segundo apellido', 'Tipo de identificación', 'Nro. de identificación', 'Género', 'Correo electrónico', 'Fecha de registro', 'Tipo de usuario', 'Estado'];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center']
            ]
        ];
    }
}
