<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TurnamenFilterRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $tab = $this->query('tab', 'akan_datang');

        if (! is_string($tab) || ! in_array($tab, ['akan_datang', 'berakhir', 'arsip'], true)) {
            $this->merge(['tab' => 'akan_datang']);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tab' => ['nullable', Rule::in(['akan_datang', 'berakhir', 'arsip'])],
            'q' => ['nullable', 'string', 'max:100'],
        ];
    }
}
