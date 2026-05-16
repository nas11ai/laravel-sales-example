<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal'              => ['required', 'date'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.item_id'      => ['required', 'exists:items,id'],
            'items.*.qty'          => ['required', 'integer', 'min:1'],
        ];
    }
}
