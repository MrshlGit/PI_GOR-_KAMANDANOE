<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin\Turnamen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTurnamenStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([Turnamen::STATUS_APPROVED, Turnamen::STATUS_REJECTED])],
        ];
    }
}
