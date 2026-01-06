<?php

namespace App\View\Components;

use Illuminate\View\Component;

class PriceAnalysis extends Component
{
    public $details;
    public $weightTotal;

    public function __construct($details,$weightTotal)
    {
        $this->details = $details;
        $this->weightTotal = $weightTotal;
    }

    public function render()
    {
        return view('components.price-analysis');
    }
}