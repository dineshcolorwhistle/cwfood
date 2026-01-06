<?php

namespace App\View\Components;

use Illuminate\View\Component;

class IngredientsTable extends Component
{
    public $groupedIngredients;
    public $batchTotal;
    public $product;

    public function __construct($groupedIngredients,$batchTotal,$product)
    {
        $this->groupedIngredients = $groupedIngredients;
        $this->batchTotal = $batchTotal;
        $this->product = $product;
    }

    public function render()
    {
        return view('components.ingredients-table');
    }
}