<?php

namespace App\Exports;

use App\Models\Fsanz;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FSANZExport implements FromCollection,WithHeadings,WithStyles
{
    public function collection()
    {
        return Fsanz::select('food_id', 'food_name', 'energy_kj', 'protein_g', 'fat_total_g', 'fat_saturated_g', 'carbohydrate_g', 'total_sugars_g', 'sodium_mg', 'description', 'specific_gravity')->get();
    }

    public function headings(): array
    {
        return ['Food ID','Food Name','Energy (kJ)','Protein (g)','Total Fat (g)','Saturated Fat (g)','Carbohydrates (g)','Total Sugars (g)','Sodium (mg)','Description','Specific Gravity'];
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
