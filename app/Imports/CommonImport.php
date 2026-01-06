<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class CommonImport implements ToArray, WithCalculatedFormulas
{
    public function array(array $rows)
    {
        return $rows; // Returns the sheet data with calculated values
    }
}
