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

        $frais = Frais::find($this->request->get('fee_id'));
        return [
            'student_id' => ['required', 'exists:eleves,id', 'string',],
            'fee_id' => ['required', 'integer', 'distinct','exists:frais,id',],
            'amount' => ['required', 'numeric', 'gt:0',],
            'period' => [Rule::requiredIf(function () use ($frais) {
                return $frais?->type == FraisType::MINERVAL;
            }), 'numeric', 'between:1,12',],
            'currency' => ['required', 'string', 'in:USD,CDF',],
            'rate' => [
                Rule::requiredIf(function () use ($frais) {
                    return $frais?->devise->value != $this->request->get('currency');
                }),
                'numeric', 'gt:0',],
            'paid_at' => ['required', 'date','before_or_equal:today']
        ];
    }

    public function messages(): array
    {
        return [
            'period.required' => 'Period is required when fee is MINERVAL',
            'rate.required' => 'Exchange rate is required when currency is different from fee currency',
        ];
    }
}
