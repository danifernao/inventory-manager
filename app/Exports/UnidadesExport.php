<?php

namespace App\Exports;

use App\Models\Unidad;
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

class UnidadesExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStrictNullComparison, WithStyles
{
    public function __construct($coleccion)
    {
        $this->unidades = $coleccion;
    }
    
    public function collection()
    {
        return $this->unidades;
    }
    
    public function map($unidad): array
    {
        return [
            $unidad->numero_serie,
            $unidad->proveedor ? $unidad->proveedor->nombre : '',
            $unidad->fecha_adquisicion ? Date::dateTimeToExcel($unidad->fecha_adquisicion) : '',
            $unidad->valor_adquisicion,
            $unidad->valor_residual,
            $unidad->depreciacion,
            $unidad->dado_de_baja ? 'Sí' : 'No',
            $unidad->motivo_de_baja
        ];
    }
    
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_DATE_DDMMYYYY
        ];
    }
    
    public function headings(): array
    {
        return ['Nro. de serie', 'Proveedor', 'Fecha de adquisición', 'Valor de adquisición', 'Valor residual', 'Depreciación', 'Dado de baja', 'Motivo de baja'];
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