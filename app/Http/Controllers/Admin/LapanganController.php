<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScheduleFilterRequest;
use App\Http\Requests\Admin\StoreLapanganRequest;
use App\Http\Requests\Admin\UpdateLapanganRequest;
use App\Http\Requests\Admin\UpdateScheduleBookingStatusRequest;
use App\Models\Admin\Booking;
use App\Models\Admin\Lapangan;
use App\Services\Admin\BookingManagementService;
use App\Services\BookingPaymentVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LapanganController extends Controller
{
    public function index(ScheduleFilterRequest $request): View
    {
        $tanggal = $request->validated()['tanggal'] ?? today()->toDateString();

        $lapangans = Lapangan::orderBy('id_lapangan')->get();
        $jam = range(7, 22);
        $jadwal = Lapangan::jadwalUntukTanggal($tanggal, $jam);

        return view('admin.Manajemen-lapangan.index', compact('tanggal', 'jam', 'jadwal', 'lapangans'));
    }

    public function updateBookingStatus(
        UpdateScheduleBookingStatusRequest $request,
        Booking $booking,
        BookingPaymentVerificationService $paymentVerification,
        BookingManagementService $bookingManagement
    ): RedirectResponse {
        $data = $request->validated();

        $verifiedCount = $paymentVerification->verifyPendingPaymentForConfirmation(
            (string) $booking->getKey(),
            $data['status_booking']
        );
        if ($verifiedCount !== null) {
            if ($verifiedCount === 0) {
                return redirect()->route('admin.lapangan.index', ['tanggal' => $data['tanggal']])
                    ->with('galat', 'Bukti pembayaran tidak tersedia atau pemesanan sudah diproses.');
            }

            return redirect()->route('admin.lapangan.index', ['tanggal' => $data['tanggal']])
                ->with('sukses', $verifiedCount === 1
                    ? 'Pembayaran berhasil diverifikasi.'
                    : "Pembayaran untuk {$verifiedCount} pemesanan berhasil diverifikasi.");
        }

        $updated = $bookingManagement->updatePendingStatus($booking, $data['status_booking']);

        if (! $updated) {
            return redirect()->route('admin.lapangan.index', ['tanggal' => $data['tanggal']])
                ->with('galat', 'Pemesanan ini sudah diproses sebelumnya.');
        }

        $pesan = $data['status_booking'] === 'dikonfirmasi'
            ? 'Pemesanan berhasil dikonfirmasi.'
            : 'Pemesanan berhasil ditolak.';

        return redirect()->route('admin.lapangan.index', ['tanggal' => $data['tanggal']])
            ->with('sukses', $pesan);
    }

    public function store(StoreLapanganRequest $request): RedirectResponse
    {
        Lapangan::create($request->validated());

        return redirect()->route('admin.lapangan.index')
            ->with('sukses', 'Lapangan berhasil ditambahkan.');
    }

    public function update(UpdateLapanganRequest $request, Lapangan $lapangan): RedirectResponse
    {
        $lapangan->update($request->validated());

        return redirect()->route('admin.lapangan.index')
            ->with('sukses', 'Konfigurasi lapangan berhasil diperbarui.');
    }

    public function destroy(Lapangan $lapangan): RedirectResponse
    {
        if (Booking::where('id_lapangan', $lapangan->getKey())->exists()) {
            return redirect()->route('admin.lapangan.index')
                ->with('galat', 'Lapangan tidak dapat dihapus karena memiliki riwayat pemesanan. Nonaktifkan lapangan jika tidak digunakan lagi.');
        }

        $lapangan->delete();

        return redirect()->route('admin.lapangan.index')
            ->with('sukses', 'Lapangan berhasil dihapus.');
    }
}
