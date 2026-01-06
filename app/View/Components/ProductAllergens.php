<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ProductAllergens extends Component
{
    public $prodIngs;

    /**
     * Create a new component instance.
     *
     * @param $product
     * @return void
     */
    public function __construct($prodIngs)
    {
        $this->prodIngs = $prodIngs;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function render()
    {
        return view('components.product-allergens');
    }
}
