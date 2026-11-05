<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLapanganRequest extends FormRequest
{
    protected $errorBag = 'ubah';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_lapangan' => ['required', 'string', 'max:20'],
            'tipe_lantai' => ['required', 'string', 'max:100'],
            'harga_per_jam' => ['required', 'numeric', 'min:0'],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:32'],
            'status_lapangan' => ['required', Rule::in(['Aktif', 'Nonaktif'])],
        ];
    }
}
