<?php

namespace App\View\Components;

use Illuminate\View\Component;

class LabellingPackage extends Component
{
    public $prodLabel;
    public function __construct($prodLabel)
    {
        $this->prodLabel = $prodLabel;
    }

    public function render()
    {
        return view('components.labelling.package');
    }
}