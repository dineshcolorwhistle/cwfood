<?php

namespace App\View\Components;

use Illuminate\View\Component;

class NutritionalInformation extends Component
{
    public $product;
    public $allergen;

    public function __construct($product, $allergen)
    {
        // Accept the product and allergen data when the component is used
        $this->product = $product;
        $this->allergen = $allergen;
    }

    public function render()
    {
        return view('components.nutritional-information');
    }
}
