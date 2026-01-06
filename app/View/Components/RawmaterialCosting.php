<?php

namespace App\View\Components;

use Illuminate\View\Component;

class RawmaterialCosting extends Component
{
    public $nutrition;
    public $directcost;
   
    public function __construct($nutrition,$directcost)
    {
        // Accept the nutrition data when the component is used
        $this->nutrition = $nutrition;
        $this->directcost = $directcost;         
    }

    public function render()
    {
        return view('components.costing.rawmaterial-costing');
    }
}