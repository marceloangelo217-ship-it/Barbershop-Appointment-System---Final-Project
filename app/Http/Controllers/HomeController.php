<?php

namespace App\Http\Controllers;

use App\Services\BookingData;

class HomeController extends Controller
{
    public function index()
    {
        return view('pages.home', [
            'services' => BookingData::SERVICES,
            'barbers' => BookingData::BARBERS,
        ]);
    }
}
