<?php

namespace App\Exports;

use App\Models\Proyecto;
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

class ProyectosExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStrictNullComparison, WithStyles
{
    public function __construct($coleccion)
    {
        $this->proyectos = $coleccion;
    }
    
    public function collection()
    {
        return $this->proyectos;
    }
    
    public function map($proyecto): array
    {
        return [
            $proyecto->nombre,
            $proyecto->codigo,
            $proyecto->ubicacion,
            $proyecto->fecha_apertura ? Date::dateTimeToExcel($proyecto->fecha_apertura) : '',
            $proyecto->fecha_clausura ? Date::dateTimeToExcel($proyecto->fecha_clausura) : '',
            $proyecto->esta_activo ? 'Sí' : 'No',
            $proyecto->unidades_count
        ];
    }
    
    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'E' => NumberFormat::FORMAT_DATE_DDMMYYYY
        ];
    }
    
    public function headings(): array
    {
        return ['Nombre', 'Código', 'Ubicación', 'Fecha de apertura', 'Fecha de clausura', 'Estado', 'Nro. de unidades'];
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