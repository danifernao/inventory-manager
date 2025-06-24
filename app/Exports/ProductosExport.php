<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductosExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStrictNullComparison, WithStyles
{
    public function __construct($coleccion)
    {
        $this->productos = $coleccion;
    }
    
    public function collection()
    {
        return $this->productos;
    }
    
    public function map($producto): array
    {
        return [
            $producto->fabricante,
            $producto->marca,
            $producto->modelo,
            $producto->unidades_count
        ];
    }
    
    public function headings(): array
    {
        return ['Fabricante', 'Marca', 'Modelo', 'Nro. de unidades'];
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
