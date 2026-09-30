<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TicketRequestController extends Controller
{
    public function index()
    {
        return view('pages.request.alltickets');
    }

    public function store()
    {
        return view('pages.request.showticket');
    }
}
