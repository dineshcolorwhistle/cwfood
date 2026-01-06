<?php

namespace App\View\Components;

use Illuminate\View\Component;

class NutritionAnalysis extends Component
{
    public $product;
    public $prodIngredient;

    public function __construct($product,$prodIngredient)
    {
        // Accept the product data when the component is used
        $this->product = $product;
        $this->prodIngredient = $prodIngredient;
    }

    public function render()
    {
        return view('components.labelling.nutrition-analysis');
    }
}
