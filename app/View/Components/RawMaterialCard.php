<?php

namespace App\View\Components;

use Illuminate\View\Component;

class RawMaterialCard extends Component
{
    public $rawMaterial;

    /**
     * Create a new component instance.
     */
    public function __construct($rawMaterial)
    {
        $this->rawMaterial = $rawMaterial;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.raw-material-card');
    }
}
