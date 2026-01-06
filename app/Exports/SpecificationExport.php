<?php

namespace App\Exports;

use App\Models\Specification;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Session;

class SpecificationExport implements FromCollection, WithHeadings, WithStyles
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
        $collection = Specification::where('client_id', $clientID)->where('workspace_id', $workspaceID)->select($this->db_headings)->get();
        $yesNoFields = [
            'cool_aus_made_claim',
            'cool_aus_owned_claim',
            'cool_aus_grown_claim',
            'trace_document_required',
            'cert_is_organic',
            'cert_is_halal',
            'cert_is_kosher',
            'cert_is_gluten_free',
            'cert_is_non_gmo',
            'cert_is_fair_trade',
        ];
        $updated = $collection->map(function ($item) use ($yesNoFields) {
            if ($item->cool_percentage_australia) {
                $item['cool_percentage_australia'] = $item->cool_percentage_australia * 100;
            }
            foreach ($yesNoFields as $field) {
                $item[$field] = isset($item->$field) && $item->$field == 1 ? 'Yes' : 'No';
            }

            if ($item->spec_type) {
                switch ($item->spec_type) {
                    case 'raw_material':
                        $type = "Raw Material";
                        break;
                    case 'product':
                        $type = "Finished Product";
                        break;
                    case 'package_material':
                        $type = "Packaging Material";
                        break;
                    default:
                        $type = "Raw Material";
                        break;
                }
                $item['spec_type'] = $type;
            }

            if ($item->nutritional_basis) {
                switch ($item->nutritional_basis) {
                    case 'g':
                        $nutrition = "Per 100g";
                        break;
                    case 'ml':
                        $nutrition = "Per 100ml";
                        break;
                    default:
                        $nutrition = "Per 100g";
                        break;
                }
                $item['nutritional_basis'] = $nutrition;
            }

            if ($item->id_barcode_type) {
                switch ($item->id_barcode_type) {
                    case '1d':
                        $bar = "1D";
                        break;
                    case '2d':
                        $bar = "2D";
                        break;
                    case 'qr':
                        $bar = "QR Type";
                        break;
                    default:
                        $bar = "1D";
                        break;
                }
                $item['id_barcode_type'] = $bar;
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
