<?php

namespace App\Http\Requests;

use App\Enums\SaleStatus;
use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_id' => ['required', 'exists:sales,id'],
            'tanggal' => ['required', 'date'],
            'jumlah'  => ['required', 'integer', 'min:1'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $sale = Sale::find($this->sale_id);

                if (! $sale) return;

                if ($sale->status === SaleStatus::PAID) {
                    $validator->errors()->add('sale_id', 'Penjualan ini sudah lunas.');
                }

                $sisa = $sale->total_amount - $sale->totalPaid();
                if ($this->jumlah > $sisa) {
                    $validator->errors()->add(
                        'jumlah',
                        "Jumlah pembayaran melebihi sisa tagihan (Rp " . number_format($sisa, 0, ',', '.') . ")."
                    );
                }
            },
        ];
    }
}
