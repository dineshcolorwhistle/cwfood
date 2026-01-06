<?php

namespace App\Exports;

use App\Models\SupportTicket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TicketsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return SupportTicket::with([
            'ClientDetails',
            'WorkspaceDetails',
            'RequesterDetails',
            'AssigneeDetails',
        ])->get();
    }

    public function map($ticket): array
    {
        return [
            $ticket->id,
            $ticket->ticket_number,
            $ticket->topic,
            $ticket->email,
            $ticket->category,
            $ticket->description,
            $ticket->status,
            $ticket->priority,
            $ticket->due_date,

            // IMAGE count or file (optional)
            $ticket->ticket_image,

            // 🔥 Replace ID → Name using relations
            $ticket->ClientDetails->name ?? '',
            $ticket->WorkspaceDetails->name ?? '',
            $ticket->RequesterDetails->name ?? '',
            $ticket->AssigneeDetails->name ?? '',

            // If CC stored as comma separated IDs, convert to names
            $this->formatCCNames($ticket->ccs),

            $ticket->created_at?->format('Y-m-d H:i:s'),
            $ticket->updated_at?->format('Y-m-d H:i:s'),
            $ticket->time_estimated,
            $ticket->time_spent,
        ];
    }

    public function headings(): array
    {
        return [
            'ID','Ticket Number','Topic','Email','Category','Description','Status','Priority',
            'Due Date','Ticket Image','Client','Workspace','Requester','Assignee','CCs',
            'Created At','Updated At','Time Estimated','Time Spent'
        ];
    }

    //================= CC Handling =================//
    private function formatCCNames($ccs)
    {
        if(!$ccs) return '';

        $ids = explode(',', $ccs);

        $users = \App\Models\User::whereIn('id', $ids)->pluck('name')->toArray();

        return implode(', ', $users);
    }
}
