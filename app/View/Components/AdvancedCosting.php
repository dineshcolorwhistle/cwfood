<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AdvancedCosting extends Component
{
    public $product;
    public $directcost; 

    public function __construct($product,$directcost)
    {
        $this->product = $product;
        $this->directcost = $directcost;
    }

    public function render()
    {
        return view('components.advanced-costing');
    }
}