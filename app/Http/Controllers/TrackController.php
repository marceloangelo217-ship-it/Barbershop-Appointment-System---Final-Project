<?php

namespace App\Http\Controllers;

use App\Services\JsonStorageService;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function __construct(private JsonStorageService $store) {}

    public function index(Request $request)
    {
        $ref = strtoupper(trim((string)$request->query('ref', '')));
        $booking = null;
        $searched = $ref !== '';
        if ($searched) {
            foreach ($this->store->read('bookings.json') as $b) {
                if (strtoupper($b['ref'] ?? '') === $ref) { $booking = $b; break; }
            }
        }
        return view('pages.track', compact('ref','booking','searched'));
    }
}
