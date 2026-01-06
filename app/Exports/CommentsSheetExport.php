<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CommentsSheetExport implements FromArray, WithHeadings, WithTitle, WithEvents
{
    public function array(): array
    {
        // Get table names from models to ensure correct names
        $ticketTable = (new \App\Models\support_ticket)->getTable();
        $commentTable = (new \App\Models\support_ticket_comment)->getTable();
        
        // Direct join query to ensure ticket data is included
        $comments = DB::table($commentTable)
            ->join($ticketTable, "{$commentTable}.ticket_id", '=', "{$ticketTable}.id")
            ->leftJoin('users', "{$commentTable}.created_by", '=', 'users.id')
            ->select(
                "{$ticketTable}.ticket_number",
                "{$ticketTable}.topic",
                'users.name as commenter_name',
                "{$commentTable}.name as comment_name",
                "{$commentTable}.description",
                "{$commentTable}.created_at"
            )
            ->orderBy("{$ticketTable}.updated_at", 'desc')  // Order by ticket updated_at
            ->orderBy("{$commentTable}.created_at", 'desc') // Then by comment created_at
            ->get();

        $rows = [];
        foreach ($comments as $comment) {
            $rows[] = [
                $comment->ticket_number ?? '',
                strip_tags($comment->topic ?? ''),
                $comment->commenter_name ?? $comment->comment_name ?? '',
                strip_tags($comment->description ?? ''),
                $comment->created_at ? date('Y-m-d H:i', strtotime($comment->created_at)) : '',
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Comments';
    }

    public function headings(): array
    {
        return [
            'Ticket No',
            'Topic',
            'Commenter',
            'Comment',
            'Comment Date'
        ];
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
                $sheet->getColumnDimension('A')->setWidth(12);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(60);
                $sheet->getColumnDimension('E')->setWidth(18);

                // Header row - bold only, no fill
                $sheet->getStyle('A1:E1')->getFont()->setBold(true)->setSize(12);

                // Wrap text for Topic and Comment
                $sheet->getStyle("B1:B{$highestRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("D1:D{$highestRow}")->getAlignment()->setWrapText(true);
            }
        ];
    }
}

