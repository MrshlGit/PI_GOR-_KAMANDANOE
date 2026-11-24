<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookingFilterRequest;
use App\Http\Requests\Admin\StoreOfflineBookingRequest;
use App\Http\Requests\Admin\UpdateBookingStatusRequest;
use App\Models\Admin\Booking;
use App\Models\Admin\Lapangan;
use App\Services\BookingPaymentVerificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PemesananController extends Controller
{
    public function index(BookingFilterRequest $request): View
    {
        $filter = $request->validated();
        $bookings = Booking::daftarAdmin($filter)
            ->orderByDesc('tgl_main')
            ->orderByDesc('jam_mulai')
            ->paginate(10)
            ->withQueryString();

        $this->attachPaymentProofs($bookings);

        return view('admin.pemesanan.index', [
            'bookings' => $bookings,
            'lapangans' => Lapangan::orderBy('nama_lapangan')->get(),
            'filter' => $filter,
            'statistik' => Booking::statistikAdmin(),
            'statusList' => Booking::STATUS,
        ]);
    }

    public function store(StoreOfflineBookingRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $lapangan = Lapangan::findOrFail($data['id_lapangan']);
        if (strtolower((string) $lapangan->status_lapangan) === 'nonaktif') {
            throw ValidationException::withMessages([
                'id_lapangan' => 'Lapangan ini sedang tidak tersedia untuk pemesanan.',
            ]);
        }

        if ($lapangan->harga_per_jam === null) {
            throw ValidationException::withMessages([
                'id_lapangan' => 'Harga lapangan ini belum diatur.',
            ]);
        }

        $mulai = Carbon::createFromFormat('Y-m-d H:i', $data['tgl_main'].' '.$data['jam_mulai']);
        $selesai = $mulai->copy()->addHours((int) $data['durasi']);
        if (Booking::adaBentrok($lapangan->getKey(), $data['tgl_main'], $mulai, $selesai)) {
            throw ValidationException::withMessages([
                'jam_mulai' => 'Jadwal lapangan sudah terisi pada rentang waktu tersebut.',
            ]);
        }

        DB::transaction(function () use ($request, $data, $lapangan): void {
            Booking::create([
                'id_user' => $request->user()->getAuthIdentifier(),
                'id_lapangan' => $lapangan->getKey(),
                'nama_pemesan' => $data['nama_pemesan'],
                'no_hp_pemesan' => $data['no_hp_pemesan'],
                'tgl_main' => $data['tgl_main'],
                'jam_mulai' => $data['jam_mulai'].':00',
                'durasi' => (int) $data['durasi'],
                'status_booking' => 'dikonfirmasi',
                'total_bayar' => round((float) $lapangan->harga_per_jam * (int) $data['durasi'], 2),
            ]);
        });

        return redirect()->route('admin.pemesanan.index')
            ->with('sukses', 'Pemesanan offline berhasil dicatat.');
    }

    public function updateStatus(
        UpdateBookingStatusRequest $request,
        Booking $booking,
        BookingPaymentVerificationService $paymentVerification
    ): RedirectResponse {
        $data = $request->validated();
        $routeParameters = $request->only(['q', 'status', 'tanggal', 'id_lapangan']);

        if ($data['status_booking'] === 'dikonfirmasi') {
            $bookingId = (string) $booking->getKey();
            if ($paymentVerification->hasPendingPaymentForBooking($bookingId)) {
                $verifiedCount = $paymentVerification->verifyForBooking($bookingId);

                if ($verifiedCount === 0) {
                    return redirect()->route('admin.pemesanan.index', $routeParameters)
                        ->with('galat', 'Bukti pembayaran tidak tersedia atau pemesanan sudah diproses.');
                }

                return redirect()->route('admin.pemesanan.index', $routeParameters)
                    ->with('sukses', $verifiedCount === 1
                        ? 'Pembayaran berhasil diverifikasi.'
                        : "Pembayaran untuk {$verifiedCount} pemesanan berhasil diverifikasi.");
            }
        }

        $booking->update($data);

        return redirect()->route('admin.pemesanan.index', $routeParameters)
            ->with('sukses', 'Status pemesanan berhasil diperbarui.');
    }

    private function attachPaymentProofs(LengthAwarePaginator $bookings): void
    {
        $bookingIds = $bookings->getCollection()->pluck('id_booking')->all();
        $proofByBooking = [];

        if ($bookingIds !== [] && Schema::hasTable('pembayaran') && Schema::hasTable('detail_item_booking')) {
            $proofs = DB::table('pembayaran as payment')
                ->join(
                    'detail_item_booking as booking_detail',
                    'booking_detail.id_detail',
                    '=',
                    'payment.id_detail_item_booking'
                )
                ->whereIn('booking_detail.id_booking', $bookingIds)
                ->orderByDesc('payment.tgl_bayar')
                ->orderByDesc('payment.id_pembayaran')
                ->get([
                    'booking_detail.id_booking',
                    'payment.id_pembayaran',
                    'payment.bukti_transfer',
                    'payment.status_pembayaran',
                ]);

            foreach ($proofs as $proof) {
                $idBooking = (string) $proof->id_booking;
                if (isset($proofByBooking[$idBooking])) {
                    continue;
                }

                $filename = basename(str_replace('\\', '/', (string) $proof->bukti_transfer));
                $proof->available = $filename !== ''
                    && is_file(public_path('uploads/bukti_pembayaran/'.$filename));
                $proofByBooking[$idBooking] = $proof;
            }
        }

        $bookings->getCollection()->each(function (Booking $booking) use ($proofByBooking): void {
            $booking->setAttribute(
                'payment_proof',
                $proofByBooking[(string) $booking->id_booking] ?? null
            );
        });
    }
}
