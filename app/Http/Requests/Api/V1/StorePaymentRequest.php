<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\FraisType;
use App\Models\Frais;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:eleves,id', 'string',],
            'fee_id' => ['required', 'integer', 'distinct','exists:frais,id',],
            'amount' => ['required', 'numeric', 'gt:0',],
            'period' => [Rule::requiredIf(function () {
                return Frais::find($this->request->get('fee_id'))->type == FraisType::MINERVAL;
            }), 'numeric', 'between:1,12',],
            'currency' => ['required', 'string', 'in:USD,CDF',],
            'paid_at' => ['required', 'date','before_or_equal:today']
        ];
    }

    public function messages(): array
    {
        return [
            'period.required' => 'Period is required when fee is MINERVAL',
        ];
    }
}
