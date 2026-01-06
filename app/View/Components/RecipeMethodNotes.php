<?php

namespace App\View\Components;

use Illuminate\View\Component;

class RecipeMethodNotes extends Component
{
    public $product;

    public function __construct($product)
    {
        // Pass the product to the component
        $this->product = $product;
    }

    public function render()
    {
        return view('components.recipe-method-notes');
    }
}