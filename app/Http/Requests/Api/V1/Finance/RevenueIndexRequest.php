<?php

namespace App\Http\Requests\Api\V1\Finance;

use Illuminate\Foundation\Http\FormRequest;

class RevenueIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'period' => ['nullable', 'in:day,month,year'],
            'startDate' => ['nullable', 'date_format:Y-m-d', 'required_with:endDate'],
            'endDate' => ['nullable', 'date_format:Y-m-d', 'required_with:startDate', 'after_or_equal:startDate'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
