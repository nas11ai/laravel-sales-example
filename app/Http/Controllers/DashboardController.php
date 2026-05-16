<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Sale::class);

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        // Widgets
        $salesQuery     = Sale::whereBetween('tanggal', [$startDate, $endDate]);
        $totalTransaksi = (clone $salesQuery)->count();
        $totalPenjualan = (int) (clone $salesQuery)->sum('total_amount');
        $totalQty       = (int) SaleItem::whereHas(
            'sale',
            fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate])
        )->sum('qty');

        // Chart penjualan per bulan (12 bulan terakhir, fixed range)
        $driver = DB::connection()->getDriverName();

        $yearExpr  = $driver === 'sqlite' ? "strftime('%Y', tanggal)" : 'YEAR(tanggal)';
        $monthExpr = $driver === 'sqlite' ? "strftime('%m', tanggal)" : 'MONTH(tanggal)';

        $penjualanPerBulan = Sale::selectRaw("
            {$yearExpr}  AS year,
            {$monthExpr} AS month,
            SUM(total_amount) AS total
        ")
            ->groupByRaw("{$yearExpr}, {$monthExpr}")
            ->orderByRaw("{$yearExpr}, {$monthExpr}")
            ->get()
            ->map(fn($row) => [
                'label' => Carbon::create($row->year, $row->month)->translatedFormat('M Y'),
                'total' => (int) $row->total,
            ]);

        // Chart qty per item (dalam range filter)
        $qtyPerItem = SaleItem::with('item:id,nama')
            ->whereHas(
                'sale',
                fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate])
            )
            ->selectRaw('item_id, SUM(qty) AS total_qty')
            ->groupBy('item_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'label' => $row->item?->nama ?? 'Unknown',
                'qty'   => (int) $row->total_qty,
            ]);

        return Inertia::render('Dashboard', [
            'widgets' => [
                'total_transaksi' => $totalTransaksi,
                'total_penjualan' => $totalPenjualan,
                'total_qty'       => $totalQty,
            ],
            'charts' => [
                'penjualan_per_bulan' => $penjualanPerBulan,
                'qty_per_item'        => $qtyPerItem,
            ],
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date'   => $endDate->format('Y-m-d'),
            ],
        ]);
    }
}
