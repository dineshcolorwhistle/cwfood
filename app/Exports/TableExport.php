<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Session;

class TableExport implements FromCollection, WithHeadings, WithStyles
{
    protected $headings;
    protected $table_collection;
    public function __construct(array $headings, $table_collection)
    {        
        $this->headings = $headings;  
        $this->table_collection = $table_collection;
    }

    public function collection()
    {
        return collect($this->table_collection)->map(function ($item) {
            return (array) $item;   // FIX HERE
        });
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [ // Row 1 (heading row)
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '4CAF50'],
                ],
            ],
        ];
    }
}   

