<?php

namespace App\Exports;

use App\Models\{Client_company,Client_contact_tag,Client_contact,Client_contact_category};
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Session;

class ContactExport implements FromCollection,WithHeadings,WithStyles
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
        $collection = Client_contact::where('client_id', $clientID)->with(['ClientCompany','Category'])->latest('created_at')->select($this->db_headings)->get();
        $updated = $collection->map(function ($item) {
            if($item->Category){
                $item['contact_category'] = $item->Category->name;
            }
            if($item['contact_tags']){
                $cmpArray = json_decode($item['contact_tags']);
                $tags = Client_contact_tag::whereIn('id', $cmpArray)->pluck('name')->toArray();
                $item['contact_tags'] = implode(', ', $tags);
            } 
            if($item->ClientCompany){
                $item['company'] = $item->ClientCompany->company_name;
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
