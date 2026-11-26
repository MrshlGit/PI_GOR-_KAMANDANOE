<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FinanceReportRequest;
use App\Models\Admin\Booking;
use App\Services\Admin\BookingPaymentProofService;
use App\Services\Admin\FinanceReportService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanKeuanganController extends Controller
{
    public function index(
        FinanceReportRequest $request,
        FinanceReportService $reportService,
        BookingPaymentProofService $paymentProofs
    ): View {
        $data = $reportService->build($request->validated()['periode'] ?? 'hari');
        $data['bookings'] = Booking::query()
            ->with(['user:id,username,email', 'lapangan:id_lapangan,nama_lapangan'])
            ->orderByDesc('tgl_main')
            ->orderByDesc('jam_mulai')
            ->paginate(10)
            ->withQueryString();

        $paymentProofs->attachTo($data['bookings']);

        return view('admin.laporan-keuangan.index', $data);
    }

    public function buktiPembayaran(
        string $pembayaran,
        BookingPaymentProofService $paymentProofs
    ): BinaryFileResponse {
        $path = $paymentProofs->pathFor($pembayaran);

        return response()->file($path, [
            'Content-Type' => mime_content_type($path) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
