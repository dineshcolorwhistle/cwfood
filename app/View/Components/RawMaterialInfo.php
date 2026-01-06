<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Ingredient;

class RawMaterialinfo extends Component
{
    public $ingredient;

    // Accept an array for $ingredient, not an Ingredient model
    public function __construct(array $ingredient = [])
    {
        // Check if 'id' is present and valid before querying the database
        if (isset($ingredient['id'])) {
            $this->ingredient = Ingredient::find($ingredient['id']);
        } else {
            // Handle the case where there's no 'id'
            $this->ingredient = new Ingredient(); // Or null, based on your requirement
        }
    }


    public function render()
    {
        return view('components.raw-material-info');
    }
}
