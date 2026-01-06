<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ProductDetails extends Component
{
    public $product;

    public function __construct($product)
    {
        // Pass the product to the component
        $this->product = $product;
    }

    public function render()
    {
        return view('components.product-details');
    }
}