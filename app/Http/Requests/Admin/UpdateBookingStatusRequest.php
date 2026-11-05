<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_booking' => ['required', Rule::in(Booking::STATUS)],
        ];
    }
}
