<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Http\Resources\ItemResource;
use App\Http\Resources\SaleResource;
use App\Models\Item;
use App\Models\Sale;
use App\Services\SaleService;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{
    public function __construct(private SaleService $saleService) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Sale::class);

        $query = Sale::with('createdBy')
            ->latest('tanggal')
            ->latest('id');

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        return Inertia::render('sales/Index', [
            'sales'   => SaleResource::collection($query->get()),
            'filters' => $request->only('start_date', 'end_date'),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Sale::class);

        return Inertia::render('sales/Create', [
            'items' => ItemResource::collection(Item::orderBy('nama')->get())->resolve(),
        ]);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        Gate::authorize('create', Sale::class);

        $sale = $this->saleService->create(
            $request->validated(),
            Auth::id(),
        );

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', "Penjualan {$sale->kode} berhasil dibuat.");
    }

    public function show(Sale $sale): Response
    {
        Gate::authorize('viewAny', Sale::class);

        return Inertia::render('sales/Show', [
            'sale' => SaleResource::make(
                $sale->load('items.item', 'createdBy', 'payments')
            ),
        ]);
    }

    public function edit(Sale $sale): Response
    {
        Gate::authorize('update', $sale);

        return Inertia::render('sales/Edit', [
            'sale'  => SaleResource::make($sale->load('items.item')),
            'items' => ItemResource::collection(Item::orderBy('nama')->get())->resolve(),
        ]);
    }

    public function update(UpdateSaleRequest $request, Sale $sale): RedirectResponse
    {
        Gate::authorize('update', $sale);

        $this->saleService->update($sale, $request->validated());

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', "Penjualan {$sale->kode} berhasil diperbarui.");
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        Gate::authorize('delete', $sale);

        $sale->delete();

        return redirect()
            ->route('sales.index')
            ->with('success', "Penjualan {$sale->kode} berhasil dihapus.");
    }
}
