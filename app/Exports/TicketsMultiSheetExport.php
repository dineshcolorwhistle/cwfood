<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TicketsMultiSheetExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new TicketsSheetExport(),
            new CommentsSheetExport(),
        ];
    }
}
