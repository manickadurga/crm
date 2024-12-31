<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageControlerer extends Controller
{
    public function welcome()
    {
        return view('welcome', ['name' => 'Jane Doe']);
    }

    public function about()
    {
        return view('about');
    }
}
