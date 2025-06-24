<?php

namespace App\Exports;

use App\Models\Proveedor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProveedoresExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStrictNullComparison, WithStyles
{
    public function __construct($coleccion)
    {
        $this->proveedores = $coleccion;
    }
    
    public function collection()
    {
        return $this->proveedores;
    }
    
    public function map($proveedor): array
    {
        return [
            $proveedor->nombre,
            $proveedor->nit,
            $proveedor->ubicacion,
            $proveedor->unidades_count
        ];
    }
    
    public function headings(): array
    {
        return ['Nombre', 'NIT', 'Ubicación', 'Nro. de unidades'];
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