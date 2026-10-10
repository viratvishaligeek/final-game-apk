<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

class MainController extends Controller
{
    /**
     * Render the static frontend preview without database or API integration.
     */
    public function index()
    {
        return view('frontend.static-preview');
    }
}
