<?php

namespace App\Exports;

use App\Models\support_ticket;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TicketsSheetExport implements FromCollection, WithMapping, WithHeadings, WithTitle, WithEvents
{
    public function collection()
    {
        return support_ticket::with([
            'ClientDetails',
            'WorkspaceDetails',
            'RequesterDetails',
            'AssigneeDetails',
        ])
        ->orderByRaw("CASE WHEN status != 'Resolved' THEN 0 ELSE 1 END") 
        ->orderBy('created_at', 'desc')
        ->get();
    }

    public function title(): string
    {
        return 'Tickets';
    }

    public function headings(): array
    {
        return [
            'Client',
            'Workspace',
            'Ticket No',
            'Topic',
            'Description',
            'Status',
            'Priority',
            'Category',
            'Due Date',
            'Time Estimated',
            'Time Spent',
            'Requester',
            'Assignee',
            'CCs'
        ];
    }

    public function map($ticket): array
    {
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
            $this->convertCCSToNames($ticket->ccs),
        ];
    }

    private function convertCCSToNames($ccs)
    {
        if (!$ccs) return '';

        if (is_string($ccs) && str_starts_with(trim($ccs), '[')) {
            $ids = json_decode($ccs, true);
        } else {
            $ids = array_map('trim', explode(',', $ccs));
        }

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

                // Header row - bold only, no fill
                $sheet->getStyle('A1:N1')->getFont()->setBold(true)->setSize(12);

                // Wrap text for Topic and Description
                $sheet->getStyle("D1:D{$highestRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("E1:E{$highestRow}")->getAlignment()->setWrapText(true);
            }
        ];
    }
}

