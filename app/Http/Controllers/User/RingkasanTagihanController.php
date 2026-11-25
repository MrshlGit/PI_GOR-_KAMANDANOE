<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RingkasanTagihanController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // $bookings = $this->bookingsAwaitingPayment((int) $user->id)
        $bookings = DB::table('booking')
            ->where('id_user', $user->id)
            ->where('status_booking', 'menunggu')
            ->orderBy('tgl_main', 'desc')
            ->orderBy('jam_mulai', 'desc')
            ->get();

        $items = [];
        $subtotal = 0;

        foreach ($bookings as $item) {
            $lapangan = DB::table('lapangan')->where('id_lapangan', $item->id_lapangan)->first();

            $namaLapangan = $lapangan->nama_lapangan ?? 'Lapangan';
            $tipeLantai = $lapangan->tipe_lantai ?? '-';
            $hargaPerJam = (float) ($lapangan->harga_per_jam ?? 0);
            $durasiBooking = (int) ($item->durasi ?? 1);

            $hargaDb = (float) ($item->total_bayar ?? 0);
            $hargaBooking = ($hargaDb >= 1000) ? $hargaDb : ($hargaPerJam * $durasiBooking);

            $items[] = [
                'id_booking' => $item->id_booking,
                'nama' => $namaLapangan.' ('.$tipeLantai.') - Tgl: '.$item->tgl_main.' ('.$durasiBooking.' Jam)',
                'harga' => 'Rp '.number_format($hargaBooking, 0, ',', '.'),
            ];

            $subtotal += $hargaBooking;
        }

        $summary = [
            'tanggal' => date('d M Y'),
            'petugas' => 'System GOR',
            'subtotal' => 'Rp '.number_format($subtotal, 0, ',', '.'),
            'potongan' => 'Rp 0',
            'ppn' => 'Rp 0',
            'nilai_transaksi' => 'Rp '.number_format($subtotal, 0, ',', '.'),
            'total' => 'Rp '.number_format($subtotal, 0, ',', '.'),
        ];

        return view('ViewRingkasanTagihan', compact('items', 'summary', 'user'));
        $booking = $bookings->first();

        return view('ViewRingkasanTagihan', compact('items', 'summary', 'user', 'booking'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bukti_bayar' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'booking_ids' => ['required', 'array', 'min:1'],
            'booking_ids.*' => ['required', 'string', 'distinct'],
        ]);

        $user = auth()->user();
        $bookingIds = array_map('strval', $request->input('booking_ids'));
        $bookings = $this->bookingsAwaitingPayment((int) $user->id)
            ->whereIn('id_booking', $bookingIds)
            ->get();

        if ($bookings->count() !== count($bookingIds)) {
            return redirect()->route('pembayaran.index')
                ->with('error', 'Daftar pemesanan berubah. Muat ulang halaman sebelum mengirim pembayaran.');

        // Cek apakah masih ada booking aktif berstatus 'menunggu'
        $hasPendingBooking = DB::table('booking')
            ->where('id_user', $user->id)
            ->where('status_booking', 'menunggu')
            ->exists();

        if (!$hasPendingBooking) {
            return redirect()->back()->with('error', 'Tidak ada data pemesanan aktif.');
        }

        $file = $request->file('bukti_bayar');
        $directory = public_path('uploads/bukti_pembayaran');
        File::ensureDirectoryExists($directory);
        $namaFile = $file->hashName();
        $file->move($directory, $namaFile);

        $created = DB::transaction(function () use ($user, $bookingIds, $namaFile): bool {
            $bookings = $this->bookingsAwaitingPayment((int) $user->id)
                ->whereIn('id_booking', $bookingIds)
                ->lockForUpdate()
                ->get();

            if ($bookings->count() !== count($bookingIds)) {
                return false;
            }

            foreach ($bookings as $booking) {
                $idDetail = (string) Str::uuid();
            // GANTI BLOK DB::transaction LAMA DENGAN KODE DI BAWAH INI
            DB::transaction(function () use ($user, $namaFile): void {
                $pendingBookings = DB::table('booking')
                    ->where('id_user', $user->id)
                    ->where('status_booking', 'menunggu')
                    ->get();

                $idDetailTerakhir = null;

                DB::table('pembayaran')->insert([
                    'id_pembayaran' => (string) Str::uuid(),
                    'tgl_bayar' => now()->toDateString(),
                    'id_detail_item_booking' => $idDetail,
                    'metode_bayar' => 'QRIS / Transfer Bank',
                    'status_pembayaran' => 'Pending',
                    'bukti_transfer' => $namaFile,
                ]);
            }

            return true;
        });

        if (! $created) {
            File::delete($directory.'/'.$namaFile);
                foreach ($pendingBookings as $itemBooking) {
                    $idDetail = (string) Str::uuid();
                    $idDetailTerakhir = $idDetail;

                    $lapangan = DB::table('lapangan')->where('id_lapangan', $itemBooking->id_lapangan)->first();
                    $hargaPerJam = (float) ($lapangan->harga_per_jam ?? 0);
                    $durasiBooking = max(1, (int) ($itemBooking->durasi ?? 1));
                    $hargaDb = (float) ($itemBooking->total_bayar ?? 0);
                    $subtotalBooking = ($hargaDb >= 1000) ? $hargaDb : ($hargaPerJam * $durasiBooking);

                    DB::table('detail_item_booking')->insert([
                        'id_detail'  => $idDetail,
                        'id_booking' => $itemBooking->id_booking,
                        'subtotal'   => $subtotalBooking,
                        'id_produk'  => null,
                        'kuantitas'  => $durasiBooking,
                    ]);

                    DB::table('booking')
                        ->where('id_booking', $itemBooking->id_booking)
                        ->update([
                            'status_booking' => 'menunggu_verifikasi',
                        ]);
                }

                if ($idDetailTerakhir) {
                    DB::table('pembayaran')->insert([
                        'id_pembayaran'          => (string) Str::uuid(),
                        'tgl_bayar'              => now()->toDateString(),
                        'id_detail_item_booking' => $idDetailTerakhir,
                        'metode_bayar'           => 'QRIS / Transfer Bank',
                        'status_pembayaran'      => 'Pending',
                        'bukti_transfer'         => $namaFile,
                    ]);
                }
            });

            return redirect()->route('pembayaran.index')
                ->with('error', 'Pemesanan ini sudah dikirim untuk verifikasi.');
        }

        $request->session()->flash('booking_notification_open', true);

        return redirect()->route('pembayaran.index')
            ->with('success', 'Bukti pembayaran berhasil dikirim.');
    }

    private function bookingsAwaitingPayment(int $userId): Builder
    {
        return DB::table('booking')
            ->where('id_user', $userId)
            ->whereRaw('LOWER(status_booking) = ?', ['menunggu'])
            ->whereNotExists(function (Builder $payments): void {
                $payments->selectRaw('1')
                    ->from('detail_item_booking as booking_detail')
                    ->join(
                        'pembayaran as payment',
                        'payment.id_detail_item_booking',
                        '=',
                        'booking_detail.id_detail'
                    )
                    ->whereColumn('booking_detail.id_booking', 'booking.id_booking');
            });
    }
}
        return redirect()->back()->with('error', 'Gagal mengunggah bukti pembayaran.');
    }}
