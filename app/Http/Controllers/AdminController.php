<?php

namespace App\Http\Controllers;

use App\Services\BookingData;
use App\Services\JsonStorageService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(private JsonStorageService $store) {}

    public function index(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        $status = trim((string)$request->query('status', ''));
        $date = trim((string)$request->query('date', ''));
        $all = $this->store->read('bookings.json');
        $filtered = array_values(array_filter($all, function ($b) use ($q,$status,$date) {
            $hay = strtolower(($b['ref'] ?? '') . ' ' . ($b['name'] ?? '') . ' ' . ($b['phone'] ?? ''));
            if ($q !== '' && !str_contains($hay, strtolower($q))) return false;
            if ($status !== '' && ($b['status'] ?? '') !== $status) return false;
            if ($date !== '' && ($b['date'] ?? '') !== $date) return false;
            return true;
        }));
        usort($filtered, fn($a,$b) => strcmp(($a['date'].$a['slot']), ($b['date'].$b['slot'])));

        $today = date('Y-m-d');
        $stats = [
            'today' => count(array_filter($all, fn($b) => ($b['date'] ?? '') === $today && in_array($b['status'] ?? '', BookingData::ACTIVE, true))),
            'pending' => count(array_filter($all, fn($b) => ($b['status'] ?? '') === 'Pending')),
            'total' => count($all),
        ];
        return view('admin.index', ['bookings'=>$filtered,'q'=>$q,'status'=>$status,'date'=>$date,'stats'=>$stats,'next'=>BookingData::NEXT]);
    }

    public function updateStatus(Request $request, string $ref)
    {
        $to = $request->validate(['status' => ['required','string']])['status'];
        try {
            $this->store->mutate('bookings.json', function (&$bookings) use ($ref, $to) {
                foreach ($bookings as &$b) {
                    if (($b['ref'] ?? '') !== $ref) continue;
                    $allowed = BookingData::NEXT[$b['status'] ?? ''] ?? [];
                    if (!in_array($to, $allowed, true)) throw new \RuntimeException('Invalid status change.');
                    $b['status'] = $to;
                    return;
                }
                throw new \RuntimeException('Booking not found.');
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Unable to update booking.');
        }
        return back()->with('success', $ref . ' is now ' . $to . '.');
    }
}
