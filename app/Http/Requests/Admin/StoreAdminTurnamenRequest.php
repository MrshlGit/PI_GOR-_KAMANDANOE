<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin\Turnamen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminTurnamenRequest extends FormRequest
{
    protected $errorBag = 'buatEvent';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_turnamen' => ['required', 'string', 'max:100'],
            'kategori' => ['required', Rule::in(Turnamen::KATEGORI)],
            'tgl_pelaksanaan' => ['required', 'date', 'after_or_equal:today'],
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
            'batas_pendaftaran' => ['nullable', 'date', 'after_or_equal:today', 'before_or_equal:tgl_pelaksanaan'],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'biaya_pendaftaran' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'kuota_tim' => ['required', 'integer', 'min:2', 'max:128'],
            'hadiah' => ['nullable', 'string', 'max:150'],
            'kontak' => ['required', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_pelaksanaan.after_or_equal' => 'Tanggal pelaksanaan tidak boleh sebelum hari ini.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'batas_pendaftaran.after_or_equal' => 'Batas pendaftaran tidak boleh sebelum hari ini.',
            'batas_pendaftaran.before_or_equal' => 'Batas pendaftaran harus sebelum atau sama dengan tanggal pelaksanaan.',
            'kontak.regex' => 'Nomor kontak tidak valid.',
            'kuota_tim.min' => 'Kuota minimal 2 tim.',
        ];
    }
}
