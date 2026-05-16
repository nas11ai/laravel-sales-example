<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\Sale;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Payment::class);

        $query = Payment::with('sale')
            ->latest('tanggal')
            ->latest('id');

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        return Inertia::render('payments/Index', [
            'payments' => PaymentResource::collection($query->get()),
            'filters'  => $request->only('start_date', 'end_date'),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Payment::class);

        // Unpaid dan partial sales saja yang bisa dibayar
        $sales = Sale::whereIn('status', ['belum_dibayar', 'belum_dibayar_sepenuhnya'])
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn($sale) => [
                'id'           => $sale->id,
                'kode'         => $sale->kode,
                'tanggal'      => $sale->tanggal->format('d/m/Y'),
                'total_amount' => $sale->total_amount,
                'total_paid'   => $sale->totalPaid(),
                'sisa'         => $sale->total_amount - $sale->totalPaid(),
                'status_label' => $sale->status->label(),
            ]);

        $selectedSaleId = $request->integer('sale_id') ?: null;

        return Inertia::render('payments/Create', [
            'sales'          => $sales,
            'selectedSaleId' => $selectedSaleId,
        ]);
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        Gate::authorize('create', Payment::class);

        $payment = $this->paymentService->create($request->validated());

        return redirect()
            ->route('payments.show', $payment)
            ->with('success', "Pembayaran {$payment->kode} berhasil dibuat.");
    }

    public function show(Payment $payment): Response
    {
        Gate::authorize('viewAny', Payment::class);

        return Inertia::render('payments/Show', [
            'payment' => PaymentResource::make($payment->load('sale')),
        ]);
    }

    public function edit(Payment $payment): Response
    {
        Gate::authorize('update', $payment);

        return Inertia::render('payments/Edit', [
            'payment' => PaymentResource::make($payment->load('sale')),
        ]);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        Gate::authorize('update', $payment);

        $this->paymentService->update($payment, $request->validated());

        return redirect()
            ->route('payments.show', $payment)
            ->with('success', "Pembayaran {$payment->kode} berhasil diperbarui.");
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        Gate::authorize('delete', $payment);

        $kode = $payment->kode;
        $payment->delete();

        return redirect()
            ->route('payments.index')
            ->with('success', "Pembayaran {$kode} berhasil dihapus.");
    }
}
