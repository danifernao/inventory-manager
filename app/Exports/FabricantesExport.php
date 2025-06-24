<?php

namespace App\Exports;

use App\Models\Fabricante;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FabricantesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStrictNullComparison, WithStyles
{
    public function __construct($coleccion)
    {
        $this->fabricantes = $coleccion;
    }
    
    public function collection()
    {
        return $this->fabricantes;
    }
    
    public function map($fabricante): array
    {
        return [
            $fabricante->nombre,
            $fabricante->nit,
            $fabricante->ubicacion,
            $fabricante->unidades_count
        ];
    }
    
    public function headings(): array
    {
        return ['Nombre', 'NIT', 'ubicacion', 'Nro. de unidades'];
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
