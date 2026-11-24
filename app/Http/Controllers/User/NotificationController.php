<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function show(Request $request, string $notification): RedirectResponse
    {
        $payment = DB::table('pembayaran as payment')
            ->join(
                'detail_item_booking as booking_detail',
                'booking_detail.id_detail',
                '=',
                'payment.id_detail_item_booking'
            )
            ->join('booking', 'booking.id_booking', '=', 'booking_detail.id_booking')
            ->where('payment.id_pembayaran', $notification)
            ->where('booking.id_user', $request->user()->getAuthIdentifier())
            ->whereNotNull('payment.bukti_transfer')
            ->where('payment.bukti_transfer', '!=', '')
            ->value('payment.bukti_transfer');

        abort_if($payment === null, 404);

        $isPending = DB::table('pembayaran as payment')
            ->join(
                'detail_item_booking as booking_detail',
                'booking_detail.id_detail',
                '=',
                'payment.id_detail_item_booking'
            )
            ->join('booking', 'booking.id_booking', '=', 'booking_detail.id_booking')
            ->where('booking.id_user', $request->user()->getAuthIdentifier())
            ->where('payment.bukti_transfer', $payment)
            ->whereRaw('LOWER(payment.status_pembayaran) = ?', ['pending'])
            ->exists();
        $seenStatuses = $request->session()->get('payment_notification_states', []);
        $seenStatuses[sha1($payment)] = $isPending ? 'pending' : 'verified';
        $request->session()->put('payment_notification_states', $seenStatuses);

        return redirect()->route('tagihan.index');
    }
}
