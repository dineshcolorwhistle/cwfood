<?php

namespace App\Exports;

use App\Models\{Product,Product_category,Product_tag};
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Session;

class ProductExport implements FromCollection,WithHeadings,WithStyles
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
        $collection = Product::where('client_id', $clientID)->where('workspace_id', $workspaceID)->orderBy('favorite','desc')->latest('created_at')->select($this->db_headings)->get();
        $updated = $collection->map(function ($item) use($clientID,$workspaceID) {
            if($item['prod_category']){
                $category = Product_category::where('client_id', $clientID)->where('workspace_id',$workspaceID)->where('id',$item['prod_category'])->pluck('name');
                if(sizeof($category) >0){
                    $item['prod_category'] = $category[0];
                }
            }
            if($item['prod_tags']){
                $ingArray = $item['prod_tags'];
                $tags = Product_tag::whereIn('id', $ingArray)->pluck('name')->toArray();
                $item['prod_tags'] = implode(', ', $tags);
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
