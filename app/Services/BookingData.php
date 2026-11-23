<?php

namespace App\Services;

class BookingData
{
    public const SERVICES = [
        ['id' => 1, 'name' => 'Haircut', 'price' => 150],
        ['id' => 2, 'name' => 'Haircut + Shave', 'price' => 250],
        ['id' => 3, 'name' => 'Kids Cut', 'price' => 100],
        ['id' => 4, 'name' => 'Hair Color', 'price' => 500],
    ];

    public const BARBERS = [
        ['id' => 1, 'name' => 'Lance'],
        ['id' => 2, 'name' => 'Joshua'],
        ['id' => 3, 'name' => 'Angelo'],
    ];

    public const SLOTS = ['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
    public const ACTIVE = ['Pending','Approved','Completed'];
    public const NEXT = [
        'Pending' => ['Approved','Rejected','Cancelled'],
        'Approved' => ['Completed','Cancelled'],
    ];

    public static function service(int $id): ?array
    {
        foreach (self::SERVICES as $s) if ($s['id'] === $id) return $s;
        return null;
    }
    public static function barber(int $id): ?array
    {
        foreach (self::BARBERS as $b) if ($b['id'] === $id) return $b;
        return null;
    }
    public static function peso($n): string { return '₱' . number_format((float)$n, 2); }
    public static function t12(string $slot): string
    {
        [$h, $m] = explode(':', $slot);
        $h = (int)$h;
        return (($h % 12) ?: 12) . ':' . $m . ($h < 12 ? ' AM' : ' PM');
    }
    public static function nice(string $date): string
    {
        return date('D, M j, Y', strtotime($date));
    }
}
