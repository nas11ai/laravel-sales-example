<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Item::class);
        $items = ItemResource::collection(
            Item::latest()->get()
        );

        return Inertia::render('master/items/Index', [
            'items' => $items,
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Item::class);
        return Inertia::render('master/items/Create');
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        Gate::authorize('create', Item::class);
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')
                ->store('items', 'public');
        }

        unset($data['image']);
        Item::create($data);

        return redirect()
            ->route('master.items.index')
            ->with('success', 'Item berhasil ditambahkan.');
    }

    public function edit(Item $item): Response
    {
        Gate::authorize('update', $item);
        return Inertia::render('master/items/Edit', [
            'item' => ItemResource::make($item),
        ]);
    }

    public function update(UpdateItemRequest $request, Item $item): RedirectResponse
    {
        Gate::authorize('update', $item);
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $data['image_path'] = $request->file('image')
                ->store('items', 'public');
        }

        unset($data['image']);
        $item->update($data);

        return redirect()
            ->route('master.items.index')
            ->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        Gate::authorize('delete', $item);
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return redirect()
            ->route('master.items.index')
            ->with('success', 'Item berhasil dihapus.');
    }
}
