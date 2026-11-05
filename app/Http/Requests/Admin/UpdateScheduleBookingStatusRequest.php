<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScheduleBookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_booking' => ['required', Rule::in(['dikonfirmasi', 'batal'])],
            'tanggal' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
