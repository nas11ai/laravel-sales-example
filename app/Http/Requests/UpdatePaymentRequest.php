<?php

namespace App\Http\Requests;

use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'jumlah'  => ['required', 'integer', 'min:1'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $payment    = $this->route('payment');
                $sale       = $payment->sale;
                $currentPaid = $sale->payments()
                    ->where('id', '!=', $payment->id)
                    ->sum('jumlah');
                $sisa = $sale->total_amount - $currentPaid;

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
