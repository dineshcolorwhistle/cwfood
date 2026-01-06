<?php

namespace App\Exports;

use App\Models\{Ingredient,Ing_country,Client_company,Rawmaterial_tag,Rawmaterial_category};
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Session;
// use Illuminate\Http\Request;

class IngredientExport implements FromCollection, WithHeadings, WithStyles
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
        $collection = Ingredient::where('client_id', $clientID)->where('workspace_id', $workspaceID)->orderBy('favorite','desc')->latest('created_at')->select($this->db_headings)->get();
        $updated = $collection->map(function ($item) use($clientID,$workspaceID) {

            if($item['category']){
                $category = Rawmaterial_category::where('client_id', $clientID)->where('workspace_id',$workspaceID)->where('id',$item['category'])->pluck('name');
                if(sizeof($category) >0){
                    $item['category'] = $category[0];
                }
            }
            
            if($item['ing_tags']){
                $ingArray = json_decode($item['ing_tags']);
                $tags = Rawmaterial_tag::whereIn('id', $ingArray)->pluck('name')->toArray();
                $item['ing_tags'] = implode(', ', $tags);
            }

            if($item['country_of_origin']){
                $country = Ing_country::where('COID',$item['country_of_origin'])->pluck('full_name');
                if(sizeof($country) >0){
                    $item['country_of_origin'] = $country[0];
                }
            }
            
            if($item['supplier_name']){
                $supplier = Client_company::where('client_id', $clientID)->where('id',$item['supplier_name'])->pluck('company_name');
                if(sizeof($supplier) >0){
                    $item['supplier_name'] = $supplier[0];
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
