<?php

namespace App\Exports;

use App\Models\SupportTicketComment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CommentsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return SupportTicketComment::select(
            'id','ticket_id','name','description','comment_image','created_at','updated_at'
        )->get();
    }

    public function headings(): array
    {
        return ['ID','Ticket ID','Name','Description','Comment Image','Created At','Updated At'];
    }
}
