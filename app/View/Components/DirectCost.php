<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DirectCost extends Component
{
    public $product;
    public $costingData;
    public $weightTotal;

    public function __construct($product,$costingData,$weightTotal)
    {   
        $this->product = $product;
        $this->costingData = $costingData;
        $this->weightTotal = $weightTotal;
    }

    public function render()
    {
        return view('components.directcost');
    }
}