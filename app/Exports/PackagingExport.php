<?php

namespace App\Exports;

use App\Models\{Packaging,Client_company};
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Session;

class PackagingExport implements FromCollection, WithHeadings, WithStyles
{
    protected $headings;
    protected $db_headings;
    public function __construct(array $headings, $db_headings)
    {        
        $this->headings = $headings;
        $this->db_headings = $db_headings;
    }

    public function collection()
    {
        $allSessionData = Session::all();
        $clientID = (int)$allSessionData['client'];
        $workspaceID = (int)$allSessionData['workspace'];
        $collection = Packaging::where('client_id', $clientID)->where('workspace_id', $workspaceID)->select($this->db_headings)->get();
        $updated = $collection->map(function ($item) use($clientID){
            if($item['supplier_id']){
                $supplier = Client_company::where('client_id', $clientID)->where('id',$item['supplier_id'])->pluck('company_name');
                if(sizeof($supplier) >0){
                    $item['supplier_id'] = $supplier[0];
                }
            }
            return $item;
        });
        return $updated;
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
