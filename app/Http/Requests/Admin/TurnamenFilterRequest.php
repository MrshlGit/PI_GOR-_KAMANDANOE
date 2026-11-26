<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin\Turnamen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TurnamenFilterRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $status = $this->query('status', Turnamen::STATUS_PENDING);

        if (! is_string($status) || ! in_array($status, Turnamen::STATUS_ADMIN, true)) {
            $this->merge(['status' => Turnamen::STATUS_PENDING]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(Turnamen::STATUS_ADMIN)],
        ];
    }
}
