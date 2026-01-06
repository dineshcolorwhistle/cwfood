<?php

namespace App\Exports;

use App\Models\support_ticket;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TicketsWithCommentsExport implements FromCollection, WithMapping, WithHeadings, WithEvents
{
    public function collection()
    {
        return support_ticket::with([
            'ClientDetails',
            'WorkspaceDetails',
            'RequesterDetails',
            'AssigneeDetails',
            'comments' => function($q){
                $q->orderBy('created_at','desc'); 
            },
            'comments.creator'
        ])
        ->orderByRaw("CASE WHEN status != 'Resolved' THEN 0 ELSE 1 END") 
        ->orderBy('created_at', 'desc')
        ->get();
    }

    public function headings(): array
    {
        return [
            'Client','Workspace','Ticket No','Topic','Description','Status',
            'Priority','Category','Due Date','Time Estimated','Time Spent',
            'Requester','Assignee','CCs','Commenter','Comment','Comment Date'
        ];
    }

    public function map($ticket): array
    {
        $ccNames = $this->convertCCSToNames($ticket->ccs);

        if($ticket->comments->count() == 0){
            return [
                $ticket->ClientDetails->name ?? '',
                $ticket->WorkspaceDetails->name ?? '',
                $ticket->ticket_number,
                strip_tags($ticket->topic),
                strip_tags($ticket->description),
                $ticket->status,
                $ticket->priority,
                $ticket->category ?? '',
                $ticket->due_date ?? '',
                $ticket->time_estimated ?? '',
                $ticket->time_spent ?? '',
                $ticket->RequesterDetails->name ?? '',
                $ticket->AssigneeDetails->name ?? '',
                $ccNames,
                '',
                '',
                '',
            ];
        }

        return $ticket->comments->map(function($c) use ($ticket, $ccNames){
            return [
                $ticket->ClientDetails->name ?? '',
                $ticket->WorkspaceDetails->name ?? '',
                $ticket->ticket_number,
                strip_tags($ticket->topic),
                strip_tags($ticket->description),
                $ticket->status,
                $ticket->priority,
                $ticket->category ?? '',
                $ticket->due_date ?? '',
                $ticket->time_estimated ?? '',
                $ticket->time_spent ?? '',
                $ticket->RequesterDetails->name ?? '',
                $ticket->AssigneeDetails->name ?? '',
                $ccNames,
                $c->creator->name ?? $c->name ?? '',
                strip_tags($c->description),
                $c->created_at?->format('Y-m-d H:i'),
            ];
        })->toArray();
    }

    private function convertCCSToNames($ccs)
    {
        if (!$ccs) return '';

        // Detect JSON format
        if (is_string($ccs) && str_starts_with(trim($ccs), '[')) {
            $ids = json_decode($ccs, true);
        } else {
            // Backup: comma separated fallback
            $ids = array_map('trim', explode(',', $ccs));
        }

        // Validate numeric IDs only
        $ids = collect($ids)->filter(fn($id) => is_numeric($id))->toArray();

        if (empty($ids)) return '';

        return User::whereIn('id', $ids)->pluck('name')->join(', ');
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event){
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $range = "A1:{$highestColumn}{$highestRow}";

                // FIRST: Clear ALL styles from entire sheet
                $sheet->getStyle($range)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_NONE],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_NONE]
                    ]
                ]);

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(12);
                $sheet->getColumnDimension('D')->setWidth(30);
                $sheet->getColumnDimension('E')->setWidth(50);
                $sheet->getColumnDimension('F')->setWidth(12);
                $sheet->getColumnDimension('G')->setWidth(10);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(12);
                $sheet->getColumnDimension('J')->setWidth(15);
                $sheet->getColumnDimension('K')->setWidth(12);
                $sheet->getColumnDimension('L')->setWidth(18);
                $sheet->getColumnDimension('M')->setWidth(18);
                $sheet->getColumnDimension('N')->setWidth(25);
                $sheet->getColumnDimension('O')->setWidth(18);
                $sheet->getColumnDimension('P')->setWidth(50);
                $sheet->getColumnDimension('Q')->setWidth(16);

                // Header row - bold only, no fill
                $sheet->getStyle('A1:Q1')->getFont()->setBold(true)->setSize(12);

                // Wrap text for Topic, Description & Comment
                $sheet->getStyle("D1:D{$highestRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("E1:E{$highestRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("P1:P{$highestRow}")->getAlignment()->setWrapText(true);
            }
        ];
    }
}
