<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MonitoringDashboardController extends Controller
{
    public function index()
    {
        return view('pages.home.dashboard');
    }
}
