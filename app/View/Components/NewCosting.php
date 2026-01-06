<?php

namespace App\View\Components;

use Illuminate\View\Component;

class NewCosting extends Component
{
    public $cost_details;
    public $weightTotal;

    public function __construct($cost_details,$weightTotal)
    {
        $this->cost_details = $cost_details;
        $this->weightTotal = $weightTotal;
    }


    
    public function render()
    {
        return view('components.new-costing');
    }
}