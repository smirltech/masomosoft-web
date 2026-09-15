<?php

namespace App\Http\Requests\Api\V1\Finance;

use App\Enums\Devise;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'perception_id' => ['nullable', 'string', 'exists:perceptions,id', 'required_without:student_id'],
            'student_id' => ['nullable', 'string', 'exists:eleves,id', 'required_without:perception_id'],
            'amount' => ['required_with:student_id', 'numeric', 'gt:0'],
            'currency' => ['required_with:student_id', Rule::enum(Devise::class)],
            'paid_by' => ['nullable', 'string', 'max:255'],
        ];
    }
}
