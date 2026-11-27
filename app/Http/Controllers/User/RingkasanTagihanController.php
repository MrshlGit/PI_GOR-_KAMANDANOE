<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RingkasanTagihanController extends Controller
{
    private array $excludedStatuses = ['lunas', 'selesai', 'batal', 'dibatalkan', 'cancelled', 'paid'];

    private function getActiveBookings($userId)
    {
        $excludedStatuses = $this->excludedStatuses;

        $bookings = DB::table('booking as b')
            ->leftJoin('lapangan as l', 'b.id_lapangan', '=', 'l.id_lapangan')
            ->select(
                'b.id_booking',
                'b.id_user',
                'b.id_lapangan',
                'b.tgl_main',
                'b.jam_mulai',
                'b.status_booking',
                'b.total_bayar',
                'b.durasi',
                'l.nama_lapangan',
                'l.tipe_lantai',
                'l.harga_per_jam'
            )
            ->where('b.id_user', $userId)
            ->where(function ($query) use ($excludedStatuses) {
                $query->whereNull('b.status_booking')
                    ->orWhereNotIn(DB::raw('LOWER(TRIM(b.status_booking))'), $excludedStatuses);
            })
            ->orderBy('b.tgl_main', 'desc')
            ->orderBy('b.jam_mulai', 'desc')
            ->get();

        if ($bookings->isEmpty()) {
            $pesananIds = DB::table('detail_items')->pluck('id_pesanan')->filter()->unique()->values();
            if ($pesananIds->isNotEmpty()) {
                $bookings = DB::table('booking as b')
                    ->leftJoin('lapangan as l', 'b.id_lapangan', '=', 'l.id_lapangan')
                    ->select(
                        'b.id_booking',
                        'b.id_user',
                        'b.id_lapangan',
                        'b.tgl_main',
                        'b.jam_mulai',
                        'b.status_booking',
                        'b.total_bayar',
                        'b.durasi',
                        'l.nama_lapangan',
                        'l.tipe_lantai',
                        'l.harga_per_jam'
                    )
                    ->whereIn('b.id_booking', $pesananIds)
                    ->where(function ($query) use ($excludedStatuses) {
                        $query->whereNull('b.status_booking')
                            ->orWhereNotIn(DB::raw('LOWER(TRIM(b.status_booking))'), $excludedStatuses);
                    })
                    ->orderBy('b.tgl_main', 'desc')
                    ->orderBy('b.jam_mulai', 'desc')
                    ->get();
            }
        }

        return $bookings;
    }

    private function getDetailItemsWithCatalog()
    {
        return DB::table('detail_items as d')
            ->leftJoin('alats as a', 'd.id_alat', '=', 'a.id_alat')
            ->leftJoin('makanans as m', 'd.id_makanan', '=', 'm.id_makanan')
            ->select(
                'd.id_detail_item',
                'd.id_pesanan',
                'd.id_alat',
                'd.id_makanan',
                'd.nama_item',
                'd.jumlah',
                'd.harga_satuan',
                'd.subtotal',
                'a.nama as nama_alat',
                'a.harga_sewa as harga_alat',
                'm.nama as nama_makanan',
                'm.harga_sewa as harga_makanan'
            )
            ->orderBy('d.id_detail_item', 'asc')
            ->get();
    }

    private function hitungHargaBooking(object $item): float
    {
        $hargaPerJam   = (float) ($item->harga_per_jam ?? 0);
        $durasiBooking = max(1, (int) ($item->durasi ?? 1));
        $hargaHitung   = $hargaPerJam * $durasiBooking;
        $hargaDb       = (float) ($item->total_bayar ?? 0);

        if ($hargaHitung > 0) {
            return $hargaHitung;
        }

        if ($hargaDb > 0) {
            return $hargaDb;
        }

        return 0.0;
    }

    private function formatDetailItem(object $detail): array
    {
        if (!is_null($detail->id_makanan)) {
            $namaItem  = $detail->nama_makanan ?? $detail->nama_item ?? 'Makanan';
            $jenisItem = 'Makanan';
            $hargaRef  = (float) ($detail->harga_makanan ?? 0);
        } elseif (!is_null($detail->id_alat)) {
            $namaItem  = $detail->nama_alat ?? $detail->nama_item ?? 'Alat';
            $jenisItem = 'Alat';
            $hargaRef  = (float) ($detail->harga_alat ?? 0);
        } else {
            $namaItem  = $detail->nama_item ?? 'Item';
            $jenisItem = 'Lainnya';
            $hargaRef  = 0.0;
        }

        $jumlahItem  = max(1, (int) ($detail->jumlah ?? 1));
        $subtotalDb  = (float) ($detail->subtotal ?? 0);
        $hargaSatuan = (float) ($detail->harga_satuan ?? 0);

        $hargaItem = $subtotalDb > 0
            ? $subtotalDb
            : ($hargaSatuan > 0 ? $hargaSatuan * $jumlahItem : $hargaRef * $jumlahItem);

        return [
            'id_detail_item' => $detail->id_detail_item,
            'id_pesanan'     => $detail->id_pesanan,
            'nama'           => $namaItem,
            'jenis'          => $jenisItem,
            'jumlah'         => $jumlahItem,
            'subtotal'       => $hargaItem,
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $showUpload = $request->boolean('show_upload')
            || session('show_upload', false);

        $bookings = $this->getActiveBookings($user->id);

        $items = [];
        $subtotal = 0.0;

        foreach ($bookings as $item) {
            $namaLapangan  = $item->nama_lapangan ?? 'Lapangan';
            $tipeLantai    = $item->tipe_lantai ?? '-';
            $durasiBooking = max(1, (int) ($item->durasi ?? 1));
            $hargaBooking  = $this->hitungHargaBooking($item);

            $items[] = [
                'id_booking' => $item->id_booking,
                'nama'       => $namaLapangan,
                'jenis'      => $tipeLantai,
                'jumlah'     => 1,
                'tgl_main'   => $item->tgl_main,
                'jam_mulai'  => $item->jam_mulai,
                'durasi'     => $durasiBooking,
                'harga_raw'  => $hargaBooking,
                'harga'      => 'Rp ' . number_format($hargaBooking, 0, ',', '.'),
            ];

            $subtotal += $hargaBooking;
        }

        $detailItems = $this->getDetailItemsWithCatalog();

        foreach ($detailItems as $detail) {
            $parsed = $this->formatDetailItem($detail);

            $items[] = [
                'id_booking' => $parsed['id_pesanan'],
                'nama'       => $parsed['nama'],
                'jenis'      => $parsed['jenis'],
                'jumlah'     => $parsed['jumlah'],
                'tgl_main'   => null,
                'jam_mulai'  => null,
                'durasi'     => '-',
                'harga_raw'  => $parsed['subtotal'],
                'harga'      => 'Rp ' . number_format($parsed['subtotal'], 0, ',', '.'),
            ];

            $subtotal += $parsed['subtotal'];
        }

        $formattedSubtotal = 'Rp ' . number_format($subtotal, 0, ',', '.');

        $summary = [
            'tanggal'         => now()->format('d M Y'),
            'petugas'         => 'System GOR',
            'subtotal'        => $formattedSubtotal,
            'potongan'        => 'Rp 0',
            'ppn'             => 'Rp 0',
            'nilai_transaksi' => $formattedSubtotal,
            'total'           => $formattedSubtotal,
        ];

        $booking = $bookings->first();

        return view('ViewRingkasanTagihan', compact(
            'items',
            'summary',
            'user',
            'booking',
            'showUpload'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bukti_bayar' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $user = auth()->user();

        $bookings = $this->getActiveBookings($user->id);
        $detailItems = $this->getDetailItemsWithCatalog();

        if ($bookings->isEmpty() && $detailItems->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data pemesanan aktif.');
        }

        $subtotalDetailByPesanan = [];
        $totalSemuaDetail = 0.0;

        foreach ($detailItems as $detail) {
            $parsed = $this->formatDetailItem($detail);
            $idPesanan = (string) ($parsed['id_pesanan'] ?? '');
            $subtotalDetailByPesanan[$idPesanan] = ($subtotalDetailByPesanan[$idPesanan] ?? 0.0) + $parsed['subtotal'];
            $totalSemuaDetail += $parsed['subtotal'];
        }

        $file = $request->file('bukti_bayar');
        $directory = public_path('uploads/bukti_pembayaran');
        File::ensureDirectoryExists($directory);
        $namaFile = $file->hashName();
        $file->move($directory, $namaFile);

        $idPembayaran = DB::transaction(function () use (
            $user,
            $bookings,
            $detailItems,
            $subtotalDetailByPesanan,
            $totalSemuaDetail,
            $namaFile
        ): string {
            $idBayar = (string) Str::uuid();
            $idDetailPertama = null;

            if ($bookings->isNotEmpty()) {
                foreach ($bookings as $index => $booking) {
                    $durasiBooking = max(1, (int) ($booking->durasi ?? 1));
                    $hargaBooking  = $this->hitungHargaBooking($booking);

                    $tambahanDetail = $subtotalDetailByPesanan[(string) $booking->id_booking] ?? 0.0;
                    $subtotalItem   = $hargaBooking + $tambahanDetail;

                    $idDetail = (string) Str::uuid();
                    if ($index === 0) {
                        $idDetailPertama = $idDetail;
                    }

                    DB::table('detail_item_booking')->insert([
                        'id_detail'  => $idDetail,
                        'id_booking' => $booking->id_booking,
                        'subtotal'   => $subtotalItem,
                        'id_produk'  => null,
                        'kuantitas'  => $durasiBooking,
                    ]);

                    DB::table('booking')
                        ->where('id_booking', $booking->id_booking)
                        ->update([
                            'status_booking' => 'menunggu_verifikasi',
                            'total_bayar'    => $subtotalItem,
                        ]);
                }
            } else {
                $fallbackBookingId = DB::table('booking')
                    ->where('id_user', $user->id)
                    ->orderByDesc('id_booking')
                    ->value('id_booking');

                if (is_null($fallbackBookingId)) {
                    $pesananIds = $detailItems->pluck('id_pesanan')->filter()->unique()->values();
                    if ($pesananIds->isNotEmpty()) {
                        $fallbackBookingId = DB::table('booking')
                            ->whereIn('id_booking', $pesananIds)
                            ->value('id_booking');
                    }
                }

                if (is_null($fallbackBookingId)) {
                    throw new \RuntimeException('Tidak ada data booking valid untuk mencatat detail_item_booking.');
                }

                $idDetailPertama = (string) Str::uuid();
                DB::table('detail_item_booking')->insert([
                    'id_detail'  => $idDetailPertama,
                    'id_booking' => $fallbackBookingId,
                    'subtotal'   => $totalSemuaDetail,
                    'id_produk'  => null,
                    'kuantitas'  => 1,
                ]);
            }

            DB::table('pembayaran')->insert([
                'id_pembayaran'          => $idBayar,
                'tgl_bayar'              => now()->toDateString(),
                'id_detail_item_booking' => $idDetailPertama,
                'metode_bayar'           => 'QRIS / Transfer Bank',
                'status_pembayaran'      => 'Pending',
                'bukti_transfer'         => $namaFile,
            ]);

            return $idBayar;
        });

        return redirect()->route('pembayaran.menunggu', ['id' => $idPembayaran])
            ->with('success', 'Bukti pembayaran berhasil dikirim! Menunggu verifikasi dari Admin.');
    }
}