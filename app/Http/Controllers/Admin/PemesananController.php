<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookingFilterRequest;
use App\Http\Requests\Admin\StoreOfflineBookingRequest;
use App\Http\Requests\Admin\UpdateBookingStatusRequest;
use App\Models\Admin\Booking;
use App\Models\Admin\Lapangan;
use App\Services\Admin\BookingManagementService;
use App\Services\Admin\BookingPaymentProofService;
use App\Services\BookingPaymentVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PemesananController extends Controller
{
    public function index(
        BookingFilterRequest $request,
        BookingPaymentProofService $paymentProofs
    ): View {
        $filter = $request->validated();
        $bookings = Booking::daftarAdmin($filter)
            ->orderByDesc('tgl_main')
            ->orderByDesc('jam_mulai')
            ->paginate(10)
            ->withQueryString();

        $paymentProofs->attachTo($bookings);

        return view('admin.pemesanan.index', [
            'bookings' => $bookings,
            'lapangans' => Lapangan::orderBy('nama_lapangan')->get(),
            'filter' => $filter,
            'statistik' => Booking::statistikAdmin(),
            'statusList' => Booking::STATUS,
        ]);
    }

    public function store(
        StoreOfflineBookingRequest $request,
        BookingManagementService $bookingManagement
    ): RedirectResponse {
        $bookingManagement->createOffline($request->validated(), $request->user());

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

        $verifiedCount = $paymentVerification->verifyPendingPaymentForConfirmation(
            (string) $booking->getKey(),
            $data['status_booking']
        );
        if ($verifiedCount !== null) {
            if ($verifiedCount === 0) {
                return redirect()->route('admin.pemesanan.index', $routeParameters)
                    ->with('galat', 'Bukti pembayaran tidak tersedia atau pemesanan sudah diproses.');
            }

            return redirect()->route('admin.pemesanan.index', $routeParameters)
                ->with('sukses', $verifiedCount === 1
                    ? 'Pembayaran berhasil diverifikasi.'
                    : "Pembayaran untuk {$verifiedCount} pemesanan berhasil diverifikasi.");
        }

        $booking->update($data);

        return redirect()->route('admin.pemesanan.index', $routeParameters)
            ->with('sukses', 'Status pemesanan berhasil diperbarui.');
    }
}
