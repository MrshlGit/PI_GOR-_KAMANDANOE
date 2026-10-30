<?php

namespace App\Models;

class Dashboard
{
    public static function emptyOverview(): array
    {
        return [
            'metrics' => [
                ['label' => 'Total Pengguna', 'value' => '0', 'note' => 'Belum ada data pengguna', 'icon' => 'users'],
                ['label' => 'Turnamen Aktif', 'value' => '0', 'note' => 'Belum ada turnamen aktif', 'icon' => 'trophy'],
                ['label' => 'Pendapatan Bulanan', 'value' => 'Rp 0', 'note' => 'Belum ada transaksi', 'icon' => 'wallet'],
                ['label' => 'Pemesanan Baru', 'value' => '0', 'note' => 'Belum ada pemesanan', 'icon' => 'calendar'],
            ],
            'bookings' => [],
            'courts' => [
                ['name' => 'Lapangan A', 'status' => 'Belum ada data', 'color' => 'orange'],
                ['name' => 'Lapangan B', 'status' => 'Belum ada data', 'color' => 'yellow'],
                ['name' => 'Lapangan C', 'status' => 'Belum ada data', 'color' => 'blue'],
                ['name' => 'Lapangan D', 'status' => 'Belum ada data', 'color' => 'pink'],
            ],
            'traffic' => [
                ['day' => 'Senin', 'male' => 0, 'total' => 0, 'female' => 0],
                ['day' => 'Selasa', 'male' => 0, 'total' => 0, 'female' => 0],
                ['day' => 'Rabu', 'male' => 0, 'total' => 0, 'female' => 0],
                ['day' => 'Kamis', 'male' => 0, 'total' => 0, 'female' => 0],
                ['day' => 'Jumat', 'male' => 0, 'total' => 0, 'female' => 0],
                ['day' => 'Sabtu', 'male' => 0, 'total' => 0, 'female' => 0],
                ['day' => 'Minggu', 'male' => 0, 'total' => 0, 'female' => 0],
            ],
        ];
    }
}
