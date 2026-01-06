<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WIPController extends Controller
{
    public function index()
    {
        return view('backend.wip.index');
    }
}
