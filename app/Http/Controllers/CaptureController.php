<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CaptureController extends Controller
{
    public function __invoke(Request $request)
    {
        return inertia()->render('Capture');
    }
}
