<?php

namespace App\Exports;

use App\Models\Movimiento;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MovimientosExport implements FromArray, ShouldAutoSize, WithHeadings, WithMapping, WithStrictNullComparison, WithStyles
{
    public function __construct($arreglo)
    {
        $this->movimientos = $arreglo;
    }
    
    public function array(): array
    {
        return $this->movimientos;
    }
    
    public function map($movimiento): array
    {
        return [
            $movimiento->fecha,
            $movimiento->fuente->nombre,
            $movimiento->destino->nombre
        ];
    }
    
    public function headings(): array
    {
        return ['Fecha', 'De', 'Hacia'];
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