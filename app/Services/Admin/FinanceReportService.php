<?php

namespace App\Services\Admin;

use App\Models\Admin\Lapangan;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FinanceReportService
{
    private const BOOKING_PAID_STATUSES = ['dikonfirmasi', 'lunas', 'selesai'];

    private const PAYMENT_PAID_STATUSES = [
        'lunas', 'terverifikasi', 'disetujui', 'approved', 'paid', 'success', 'berhasil',
    ];

    private const CANCELLED_STATUSES = ['batal', 'dibatalkan', 'cancelled'];

    private const CHART_COLORS = ['#f98a2e', '#3b82f6', '#22a06b', '#8b5cf6'];

    public function build(string $periode): array
    {
        $pendapatanBooking = (float) $this->paidBookings()->sum('booking.total_bayar');
        $pendapatanPesanan = (float) $this->paidOrders()->sum('pesanans.total');
        $customerIds = $this->customerIds();
        $lapangans = Lapangan::query()->orderBy('id_lapangan')->get();

        return [
            'jumlahPendapatan' => $pendapatanBooking + $pendapatanPesanan,
            'jumlahTransaksi' => $this->paidBookings()->count() + $this->paidOrders()->count(),
            'jumlahPelanggan' => User::query()
                ->where('role', 'user')
                ->whereIn('id', $customerIds)
                ->count(),
            'periode' => $periode,
            'grafik' => $this->grafikPendapatanLapangan($periode, $lapangans),
            'kategoriPenjualan' => $this->kategoriPenjualan($pendapatanBooking),
            'topPelanggan' => $this->topPelanggan(),
        ];
    }

    private function paidBookings(): Builder
    {
        return DB::table('booking')
            ->whereNotIn(DB::raw('LOWER(booking.status_booking)'), self::CANCELLED_STATUSES)
            ->where(function (Builder $query): void {
                $query->whereIn(
                    DB::raw('LOWER(booking.status_booking)'),
                    self::BOOKING_PAID_STATUSES
                )->orWhereExists(function (Builder $payments): void {
                    $payments->selectRaw('1')
                        ->from('detail_item_booking as booking_detail')
                        ->join(
                            'pembayaran as payment',
                            'payment.id_detail_item_booking',
                            '=',
                            'booking_detail.id_detail'
                        )
                        ->whereColumn('booking_detail.id_booking', 'booking.id_booking')
                        ->whereIn(
                            DB::raw('LOWER(payment.status_pembayaran)'),
                            self::PAYMENT_PAID_STATUSES
                        );
                });
            });
    }

    private function paidOrders(): Builder
    {
        return DB::table('pesanans')
            ->whereNotIn(DB::raw('LOWER(pesanans.status)'), self::CANCELLED_STATUSES)
            ->whereIn(
                DB::raw('LOWER(pesanans.status_pembayaran)'),
                self::PAYMENT_PAID_STATUSES
            );
    }

    private function customerIds(): array
    {
        $bookingCustomers = DB::table('booking')
            ->whereNotIn(DB::raw('LOWER(status_booking)'), self::CANCELLED_STATUSES)
            ->whereNotNull('id_user')
            ->pluck('id_user');
        $orderCustomers = DB::table('pesanans')
            ->whereNotIn(DB::raw('LOWER(status)'), self::CANCELLED_STATUSES)
            ->whereNotNull('id_user')
            ->pluck('id_user');

        return $bookingCustomers
            ->merge($orderCustomers)
            ->unique()
            ->values()
            ->all();
    }

    private function topPelanggan(): array
    {
        $totalByUser = [];
        $bookingTotals = $this->paidBookings()
            ->whereNotNull('booking.id_user')
            ->select('booking.id_user')
            ->selectRaw('SUM(booking.total_bayar) as total')
            ->groupBy('booking.id_user')
            ->get();
        $orderTotals = $this->paidOrders()
            ->whereNotNull('pesanans.id_user')
            ->select('pesanans.id_user')
            ->selectRaw('SUM(pesanans.total) as total')
            ->groupBy('pesanans.id_user')
            ->get();

        foreach ($bookingTotals->concat($orderTotals) as $row) {
            $id = (int) $row->id_user;
            $totalByUser[$id] = ($totalByUser[$id] ?? 0) + (float) $row->total;
        }

        $users = User::query()
            ->where('role', 'user')
            ->whereIn('id', array_keys($totalByUser))
            ->get(['id', 'username'])
            ->keyBy('id');
        $ranking = [];

        foreach ($totalByUser as $id => $total) {
            if ($users->has($id)) {
                $ranking[] = [
                    'username' => $users->get($id)->username,
                    'total' => $total,
                ];
            }
        }

        usort($ranking, fn (array $a, array $b): int => $b['total'] <=> $a['total']);

        return array_slice($ranking, 0, 5);
    }

    private function kategoriPenjualan(float $pendapatanBooking): array
    {
        $itemQuery = DB::table('detail_items')
            ->join('pesanans', 'pesanans.id_pesanan', '=', 'detail_items.id_pesanan')
            ->whereNotIn(
                DB::raw('LOWER(pesanans.status)'),
                self::CANCELLED_STATUSES
            )
            ->whereIn(
                DB::raw('LOWER(pesanans.status_pembayaran)'),
                self::PAYMENT_PAID_STATUSES
            );
        $makanan = (float) (clone $itemQuery)
            ->whereNotNull('detail_items.id_makanan')
            ->sum('detail_items.subtotal');
        $alat = (float) (clone $itemQuery)
            ->whereNotNull('detail_items.id_alat')
            ->sum('detail_items.subtotal');

        return [
            ['label' => 'Booking Lapangan', 'jumlah' => $pendapatanBooking, 'warna' => '#f98a2e'],
            ['label' => 'Pemesanan Konsumsi', 'jumlah' => $makanan, 'warna' => '#3b82f6'],
            ['label' => 'Penyewaan Alat', 'jumlah' => $alat, 'warna' => '#22a06b'],
        ];
    }

    private function grafikPendapatanLapangan(string $periode, Collection $lapangans): array
    {
        $rentang = $this->rentangGrafik($periode);
        $awal = $rentang[0]['awal'];
        $akhir = $rentang[count($rentang) - 1]['akhir'];
        $bucket = match ($periode) {
            'hari' => 'DATE(booking.tgl_main)',
            'bulan' => DB::connection()->getDriverName() === 'sqlite'
                ? "strftime('%Y-%m', booking.tgl_main)"
                : "DATE_FORMAT(booking.tgl_main, '%Y-%m')",
            'tahun' => DB::connection()->getDriverName() === 'sqlite'
                ? "strftime('%Y', booking.tgl_main)"
                : 'YEAR(booking.tgl_main)',
        };
        $hasil = DB::table('booking')
            ->whereIn(
                DB::raw('LOWER(booking.status_booking)'),
                self::BOOKING_PAID_STATUSES
            )
            ->whereBetween('booking.tgl_main', [$awal->toDateString(), $akhir->toDateString()])
            ->select('booking.id_lapangan')
            ->selectRaw("{$bucket} as bucket")
            ->selectRaw('SUM(booking.total_bayar) as total')
            ->groupBy('booking.id_lapangan')
            ->groupByRaw($bucket)
            ->get();
        $totalPerLapangan = [];

        foreach ($hasil as $row) {
            $totalPerLapangan[(string) $row->id_lapangan][(string) $row->bucket] = (float) $row->total;
        }

        $daftarLapangan = $lapangans->values()->map(
            function (Lapangan $lapangan, int $index): array {
                return [
                    'id' => (string) $lapangan->id_lapangan,
                    'nama' => $lapangan->nama_lapangan ?: 'Lapangan '.$lapangan->id_lapangan,
                    'warna' => self::CHART_COLORS[$index % count(self::CHART_COLORS)],
                ];
            }
        );
        $dataPeriode = array_map(
            function (array $titik) use ($totalPerLapangan, $daftarLapangan): array {
                return [
                    'label' => $titik['label'],
                    'values' => $daftarLapangan->map(
                        fn (array $lapangan): float => $totalPerLapangan[$lapangan['id']][$titik['key']] ?? 0.0
                    )->all(),
                ];
            },
            $rentang
        );

        return [
            'periods' => $dataPeriode,
            'courts' => $daftarLapangan->all(),
        ];
    }

    private function rentangGrafik(string $periode): array
    {
        $rentang = [];
        $hari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $sekarang = now();

        if ($periode === 'hari') {
            for ($offset = 6; $offset >= 0; $offset--) {
                $tanggal = $sekarang->copy()->subDays($offset);
                $rentang[] = [
                    'key' => $tanggal->toDateString(),
                    'label' => $hari[$tanggal->dayOfWeek].' '.$tanggal->day,
                    'awal' => $tanggal->copy()->startOfDay(),
                    'akhir' => $tanggal->copy()->endOfDay(),
                ];
            }
        } elseif ($periode === 'bulan') {
            for ($offset = 11; $offset >= 0; $offset--) {
                $tanggal = $sekarang->copy()->startOfMonth()->subMonths($offset);
                $rentang[] = [
                    'key' => $tanggal->format('Y-m'),
                    'label' => $bulan[$tanggal->month - 1].' '.$tanggal->year,
                    'awal' => $tanggal->copy()->startOfMonth(),
                    'akhir' => $tanggal->copy()->endOfMonth(),
                ];
            }
        } else {
            for ($offset = 4; $offset >= 0; $offset--) {
                $tanggal = $sekarang->copy()->startOfYear()->subYears($offset);
                $rentang[] = [
                    'key' => $tanggal->format('Y'),
                    'label' => $tanggal->format('Y'),
                    'awal' => $tanggal->copy()->startOfYear(),
                    'akhir' => $tanggal->copy()->endOfYear(),
                ];
            }
        }

        return $rentang;
    }
}
