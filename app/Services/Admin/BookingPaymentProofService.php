<?php

namespace App\Services\Admin;

use App\Models\Admin\Booking;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingPaymentProofService
{
    public function attachTo(LengthAwarePaginator $bookings): void
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

                $filename = $this->filename((string) $proof->bukti_transfer);
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

    public function pathFor(string $paymentId): string
    {
        $storedPath = DB::table('pembayaran')
            ->where('id_pembayaran', $paymentId)
            ->value('bukti_transfer');

        abort_if(! is_string($storedPath) || trim($storedPath) === '', 404);

        $path = public_path('uploads/bukti_pembayaran/'.$this->filename($storedPath));
        abort_unless(is_file($path), 404);

        return $path;
    }

    private function filename(string $storedPath): string
    {
        return basename(str_replace('\\', '/', $storedPath));
    }
}
