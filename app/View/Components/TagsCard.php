<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TagsCard extends Component
{
    // The product data will be passed to this component
    public $product;

    /**
     * Create a new component instance.
     *
     * @param mixed $product The product data passed to the component.
     */
    public function __construct($product)
    {
        // Initialize the product property with the passed value
        $this->product = $product;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function render()
    {
        // Return the Blade view for the component
        return view('components.tags-card');
    }
}
