<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-backoffice') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0', $this->input('type') === 'percentage' ? 'max:100' : 'max:9999999999999.99'],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')->where('tenant_id', $this->user()->tenant_id)],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', ...($this->filled('start_date') ? ['after_or_equal:start_date'] : [])],
            'start_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'end_time' => ['nullable', 'date_format:H:i,H:i:s', ...($this->filled('start_time') ? ['after_or_equal:start_time'] : [])],
            'days' => ['nullable', 'array', 'max:7'],
            'days.*' => ['required', 'distinct', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributesForDiscount(): array
    {
        return array_replace(
            array_fill_keys(['start_date', 'end_date', 'start_time', 'end_time', 'days'], null),
            $this->safe()->except('product_ids'),
            ['is_active' => $this->boolean('is_active')],
        );
    }
}
