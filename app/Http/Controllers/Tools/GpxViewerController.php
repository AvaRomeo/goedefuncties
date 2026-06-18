<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;

class GpxViewerController extends Controller
{
    public function index()
    {
        return view('gpx-viewer.index');
    }
}
