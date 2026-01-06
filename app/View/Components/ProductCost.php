<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ProductCost extends Component
{
    public $costingData;

    /**
     * Create a new component instance.
     */
    public function __construct($costingData)
    {
        $this->costingData = $costingData;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.product-cost');
    }
}
