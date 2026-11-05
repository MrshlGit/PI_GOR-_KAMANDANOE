<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfflineBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_pemesan' => ['required', 'string', 'max:100'],
            'no_hp_pemesan' => ['required', 'string', 'max:20'],
            'id_lapangan' => ['required', 'string', 'exists:lapangan,id_lapangan'],
            'tgl_main' => ['required', 'date_format:Y-m-d'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'durasi' => ['required', 'integer', 'min:1', 'max:16'],
        ];
    }
}
