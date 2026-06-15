<?php

namespace App\Http\Controllers;

class GpxViewerController extends Controller
{
    public function index()
    {
        return view('gpx-viewer.index');
    }
}
