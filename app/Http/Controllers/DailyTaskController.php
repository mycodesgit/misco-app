<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DailyTaskController extends Controller
{
    public function index()
    {
        return view('pages.task.daily');
    }
}
