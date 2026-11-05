<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(Booking::STATUS)],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'id_lapangan' => ['nullable', 'string', 'exists:lapangan,id_lapangan'],
        ];
    }
}
