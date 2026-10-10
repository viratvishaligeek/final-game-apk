<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

class MainController extends Controller
{
    /**
     * Render the static frontend preview.
     *
     * Intentionally does not query models, databases, APIs, or other backend services.
     * Replace the sample UI content when a separate backend integration phase is approved.
     */
    public function index()
    {
        return view('frontend.index');
    }
}
