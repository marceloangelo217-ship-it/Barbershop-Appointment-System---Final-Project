<?php

namespace App\Http\Controllers;

use App\Services\BookingData;
use App\Services\JsonStorageService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private JsonStorageService $store) {}

    public function create(Request $request)
    {
        $user = $request->session()->get('user');
        if (!$user) return redirect()->route('login')->with('error', 'Please log in first.');
        $draft = $request->only(['service','barber','date']);
        return view('pages.book', [
            'user' => $user,
            'services' => BookingData::SERVICES,
            'barbers' => BookingData::BARBERS,
            'slots' => BookingData::SLOTS,
            'draft' => $draft,
            'taken' => $this->takenSlots($draft),
            'today' => date('Y-m-d'),
            'maxDate' => date('Y-m-d', strtotime('+30 days')),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->session()->get('user');
        if (!$user) return redirect()->route('login')->with('error', 'Please log in first.');

        $data = $request->validate([
            'service' => ['required','integer'],
            'barber' => ['required','integer'],
            'date' => ['required','date_format:Y-m-d','after_or_equal:today','before_or_equal:' . date('Y-m-d', strtotime('+30 days'))],
            'slot' => ['required','in:' . implode(',', BookingData::SLOTS)],
            'phone' => ['required','regex:/^(09|\+639)\d{9}$/'],
            'notes' => ['nullable','string','max:200'],
        ]);

        $service = BookingData::service((int)$data['service']);
        $barber = BookingData::barber((int)$data['barber']);
        if (!$service || !$barber) return back()->withInput()->with('error', 'Invalid service or barber.');

        $slotTime = strtotime($data['date'] . ' ' . $data['slot']);
        if ($slotTime <= time()) return back()->withInput()->with('error', 'That time has already passed.');

        try {
            $ref = $this->store->mutate('bookings.json', function (&$bookings) use ($data, $service, $barber, $user) {
                foreach ($bookings as $b) {
                    $sameSlot = ($b['date'] ?? '') === $data['date'] && ($b['slot'] ?? '') === $data['slot'];
                    $active = in_array($b['status'] ?? '', BookingData::ACTIVE, true);
                    if ($sameSlot && $active && (int)($b['barber'] ?? 0) === (int)$barber['id']) {
                        throw new \RuntimeException('Sorry, that slot was just taken. Pick another time.');
                    }
                    if ($sameSlot && $active && (int)($b['uid'] ?? 0) === (int)$user['id']) {
                        throw new \RuntimeException('You already have a booking at that time.');
                    }
                }
                $ref = $this->nextRef($bookings, $data['date']);
                $bookings[] = [
                    'ref' => $ref,
                    'uid' => (int)$user['id'],
                    'name' => $user['name'],
                    'phone' => $data['phone'],
                    'notes' => trim((string)($data['notes'] ?? '')),
                    'service' => $service['name'],
                    'price' => $service['price'],
                    'barber' => $barber['id'],
                    'barberName' => $barber['name'],
                    'date' => $data['date'],
                    'slot' => $data['slot'],
                    'status' => 'Pending',
                    'createdAt' => date('c'),
                ];
                return $ref;
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Unable to save booking. Please try again.');
        }

        return redirect()->route('bookings.mine')->with('success', 'Booked! Your reference number is ' . $ref . '.');
    }

    public function mine(Request $request)
    {
        $user = $request->session()->get('user');
        if (!$user) return redirect()->route('login')->with('error', 'Please log in first.');
        $all = $this->store->read('bookings.json');
        $mine = array_values(array_filter($all, fn($b) => (int)($b['uid'] ?? 0) === (int)$user['id']));
        usort($mine, fn($a,$b) => strcmp(($b['date'].$b['slot']), ($a['date'].$a['slot'])));
        return view('pages.mine', ['bookings' => $mine]);
    }

    public function cancel(Request $request, string $ref)
    {
        $user = $request->session()->get('user');
        if (!$user) return redirect()->route('login')->with('error', 'Please log in first.');
        try {
            $this->store->mutate('bookings.json', function (&$bookings) use ($ref, $user) {
                foreach ($bookings as &$b) {
                    if (($b['ref'] ?? '') !== $ref) continue;
                    if ((int)($b['uid'] ?? 0) !== (int)$user['id']) throw new \RuntimeException('You cannot cancel this booking.');
                    if (($b['status'] ?? '') !== 'Pending') throw new \RuntimeException('Only pending bookings can be cancelled.');
                    $b['status'] = 'Cancelled';
                    return;
                }
                throw new \RuntimeException('Booking not found.');
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Unable to cancel booking.');
        }
        return back()->with('success', 'Booking cancelled. The slot is free again.');
    }

    private function takenSlots(array $draft): array
    {
        $date = $draft['date'] ?? '';
        $barber = (int)($draft['barber'] ?? 0);
        if (!$date || !$barber) return [];
        $taken = [];
        foreach ($this->store->read('bookings.json') as $b) {
            if (($b['date'] ?? '') === $date && (int)($b['barber'] ?? 0) === $barber && in_array($b['status'] ?? '', BookingData::ACTIVE, true)) {
                $taken[] = $b['slot'];
            }
        }
        return $taken;
    }

    private function nextRef(array $bookings, string $date): string
    {
        $prefix = 'BB-' . str_replace('-', '', $date) . '-';
        $max = 0;
        foreach ($bookings as $b) {
            $ref = $b['ref'] ?? '';
            if (str_starts_with($ref, $prefix)) {
                $max = max($max, (int)substr($ref, strlen($prefix)));
            }
        }
        return $prefix . str_pad((string)($max + 1), 4, '0', STR_PAD_LEFT);
    }
}
