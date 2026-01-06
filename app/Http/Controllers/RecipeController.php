<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index()
    {
        return view('backend.recipes.index');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate(Recipe::rules());

        $recipe = Recipe::create($validatedData);

        // You can add a success message here if needed
        // session()->flash('success', 'Recipe created successfully!');

        // Redirect to the next step or page
        return view('backend.recipes.index2');
    }
}
