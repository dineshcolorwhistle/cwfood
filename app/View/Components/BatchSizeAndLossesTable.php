<?php

namespace App\View\Components;

use Illuminate\View\Component;

class BatchSizeAndLossesTable extends Component
{
    public $product;
    public $batchTotal;

    public function __construct($product,$batchTotal)
    {
        $this->product = $product;
        $this->batchTotal = $batchTotal;
    }

    public function render()
    {
        return view('components.batch-size-and-losses-table');
    }
}