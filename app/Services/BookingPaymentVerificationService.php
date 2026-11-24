<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingPaymentVerificationService
{
    public function hasPendingPaymentForBooking(string $bookingId): bool
    {
        if (! Schema::hasTable('pembayaran') || ! Schema::hasTable('detail_item_booking')) {
            return false;
        }

        return DB::table('pembayaran as payment')
            ->join(
                'detail_item_booking as booking_detail',
                'booking_detail.id_detail',
                '=',
                'payment.id_detail_item_booking'
            )
            ->where('booking_detail.id_booking', $bookingId)
            ->whereRaw('LOWER(payment.status_pembayaran) = ?', ['pending'])
            ->exists();
    }

    public function verifyForBooking(string $bookingId): int
    {
        if (! Schema::hasTable('pembayaran') || ! Schema::hasTable('detail_item_booking')) {
            return 0;
        }

        $paymentId = DB::table('pembayaran as payment')
            ->join(
                'detail_item_booking as booking_detail',
                'booking_detail.id_detail',
                '=',
                'payment.id_detail_item_booking'
            )
            ->where('booking_detail.id_booking', $bookingId)
            ->whereRaw('LOWER(payment.status_pembayaran) = ?', ['pending'])
            ->value('payment.id_pembayaran');

        return $paymentId === null ? 0 : $this->verifyPayment((string) $paymentId);
    }

    public function verifyPayment(string $paymentId): int
    {
        return DB::transaction(function () use ($paymentId): int {
            $payment = DB::table('pembayaran as payment')
                ->join(
                    'detail_item_booking as booking_detail',
                    'booking_detail.id_detail',
                    '=',
                    'payment.id_detail_item_booking'
                )
                ->join('booking', 'booking.id_booking', '=', 'booking_detail.id_booking')
                ->where('payment.id_pembayaran', $paymentId)
                ->whereRaw('LOWER(payment.status_pembayaran) = ?', ['pending'])
                ->whereNotNull('payment.bukti_transfer')
                ->where('payment.bukti_transfer', '!=', '')
                ->lockForUpdate()
                ->first([
                    'payment.bukti_transfer',
                    'booking.id_user',
                ]);

            if (! $payment) {
                return 0;
            }

            $filename = basename(str_replace('\\', '/', (string) $payment->bukti_transfer));
            if ($filename === '' || ! is_file(public_path('uploads/bukti_pembayaran/'.$filename))) {
                return 0;
            }

            $payments = DB::table('pembayaran as payment')
                ->join(
                    'detail_item_booking as booking_detail',
                    'booking_detail.id_detail',
                    '=',
                    'payment.id_detail_item_booking'
                )
                ->join('booking', 'booking.id_booking', '=', 'booking_detail.id_booking')
                ->where('payment.bukti_transfer', $payment->bukti_transfer)
                ->where('booking.id_user', $payment->id_user)
                ->whereRaw('LOWER(payment.status_pembayaran) = ?', ['pending'])
                ->whereRaw('LOWER(booking.status_booking) NOT IN (?, ?, ?)', [
                    'batal', 'dibatalkan', 'cancelled',
                ])
                ->lockForUpdate()
                ->get([
                    'payment.id_pembayaran',
                    'booking.id_booking',
                ]);

            $paymentIds = $payments->pluck('id_pembayaran')->unique()->all();
            $bookingIds = $payments->pluck('id_booking')->unique()->all();

            if ($paymentIds === [] || $bookingIds === []) {
                return 0;
            }

            DB::table('pembayaran')
                ->whereIn('id_pembayaran', $paymentIds)
                ->whereRaw('LOWER(status_pembayaran) = ?', ['pending'])
                ->update(['status_pembayaran' => 'Terverifikasi']);

            DB::table('booking')
                ->whereIn('id_booking', $bookingIds)
                ->whereRaw('LOWER(status_booking) NOT IN (?, ?, ?)', [
                    'batal', 'dibatalkan', 'cancelled',
                ])
                ->update(['status_booking' => 'dikonfirmasi']);

            return count($bookingIds);
        });
    }
}
