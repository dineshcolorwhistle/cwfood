<?php

namespace App\Exports;

use App\Models\{Client_company,Client_company_tag,Client_contact,Client_company_category};
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Session;

class CompanyExport implements FromCollection,WithHeadings,WithStyles
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
        $this->db_headings[] = 'id';
        $allSessionData = Session::all();
        $clientID = (int)$allSessionData['client'];
        $collection = Client_company::where('client_id', $clientID)->with(['primaryContact','Category'])->latest('created_at')->select($this->db_headings)->get();
        $updated = $collection->map(function ($item) use($clientID) {
            if($item->Category){
                $item['company_category'] = $item->Category->name;
            }
            if($item['company_tags']){
                $cmpArray = json_decode($item['company_tags']);
                $tags = Client_company_tag::whereIn('id', $cmpArray)->pluck('name')->toArray();
                $item['company_tags'] = implode(', ', $tags);
            }
            if($item->primaryContact){
                $item['first_name'] = $item->primaryContact->first_name;
                $item['last_name'] = $item->primaryContact->last_name;
                $item['email'] = $item->primaryContact->email;
                $item['phone'] = $item->primaryContact->phone;
            }
            unset($item['id']);
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
