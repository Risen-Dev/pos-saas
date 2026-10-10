<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'manager'], true);
    }

    public function rules(): array
    {
        return [
            'base_unit' => 'sometimes|required|string|max:20',
            'allow_fraction' => 'sometimes|boolean',
            'price_tiers' => 'sometimes|array|max:10',
            'price_tiers.*.min_quantity' => 'required|numeric|decimal:0,3|min:0.001|max:100000|distinct',
            'price_tiers.*.price' => 'required|integer|min:0|max:100000000',
            'units' => 'sometimes|array|max:10',
            'units.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('product_units', 'id')->where('product_id', $this->route('product')?->id ?? 0)],
            'units.*.name' => 'required|string|max:30|distinct',
            'units.*.factor' => 'required|numeric|decimal:0,3|min:0.001|max:1000000',
            'units.*.price' => 'required|integer|min:0|max:100000000',
            'category_id' => ['required', Rule::exists('categories', 'id')->where('tenant_id', $this->user()->tenant_id)],
            'sku' => ['required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->where('tenant_id', $this->user()->tenant_id)->ignore($this->route('product'))],
            'product_name' => 'required|string|max:255',
            'type' => 'required|in:product,service',
            'sell_price' => 'required|numeric|min:0|max:100000000',
            'cost_price' => 'nullable|numeric|min:0|max:100000000',
            'stock' => 'nullable|numeric|decimal:0,3|min:0|max:1000000',
            'min_stock' => 'nullable|numeric|decimal:0,3|min:0|max:1000000',
            'manage_stock' => 'sometimes|boolean',
            'requires_preparation' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['units' => ['name', 'factor', 'price'], 'price_tiers' => ['min_quantity', 'price']] as $key => $fields) {
            if (is_array($this->input($key))) {
                $rows = array_values(array_filter($this->input($key), fn ($row) => ! is_array($row) || collect($fields)->contains(fn ($field) => filled($row[$field] ?? null))));
                $this->merge([$key => $rows]);
            }
        }
    }

    public function after(): array
    {
        return [function ($validator) {
            if ($validator->errors()->isNotEmpty() || $this->boolean('allow_fraction')) return;
            $values = array_merge([$this->input('stock', 0), $this->input('min_stock', 0)], array_column($this->input('units', []), 'factor'), array_column($this->input('price_tiers', []), 'min_quantity'));
            foreach ($values as $value) {
                if (\App\Services\RetailQuantity::ticks($value ?? 0) % 1000 !== 0) {
                    $validator->errors()->add('allow_fraction', 'Aktifkan jumlah pecahan untuk stok, kemasan, atau batas grosir yang tidak bulat.');
                    break;
                }
            }
        }];
    }

    public function catalogData(): array
    {
        $data = $this->safe()->except(['image', 'units']);
        $tracked = $data['type'] === 'product' && $this->boolean('manage_stock');

        return array_merge($data, [
            'base_unit' => $data['base_unit'] ?? 'pcs',
            'allow_fraction' => $this->boolean('allow_fraction'),
            'price_tiers' => $data['price_tiers'] ?? [],
            'requires_preparation' => $data['type'] === 'product' && $this->boolean('requires_preparation'),
            'manage_stock' => $tracked,
            'stock' => $tracked ? ($data['stock'] ?? 0) : 0,
            'min_stock' => $tracked ? ($data['min_stock'] ?? 0) : 0,
            'is_active' => $this->boolean('is_active'),
            'cost_price' => $data['cost_price'] ?? 0,
        ]);
    }
}
