<?php

namespace App\View\Components;

use Illuminate\View\Component;

class UnitsPriceTable extends Component
{
    public $product;

    public function __construct($product)
    {
        // Accept the product data when the component is used
        $this->product = $product;
    }

    public function render()
    {
        return view('components.units-price-table');
    }
}
