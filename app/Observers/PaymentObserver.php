<?php

namespace App\Observers;

use App\Enums\SaleStatus;
use App\Models\Payment;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        $this->recalculateStatus($payment);
    }

    public function deleted(Payment $payment): void
    {
        $this->recalculateStatus($payment);
    }

    private function recalculateStatus(Payment $payment): void
    {
        $sale      = $payment->sale()->withSum('payments', 'jumlah')->first();
        $totalPaid = (int) ($sale->payments_sum_jumlah ?? 0);
        $totalOwed = $sale->total_amount;

        $status = match(true) {
            $totalPaid <= 0          => SaleStatus::UNPAID,
            $totalPaid >= $totalOwed => SaleStatus::PAID,
            default                  => SaleStatus::PARTIAL,
        };

        $sale->update(['status' => $status]);
    }
}
