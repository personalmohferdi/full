<?php

namespace App\Http\Controllers;

use App\Exports\ItemsExport;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::query()
            ->with('category')
            ->orderBy('id', 'asc')
            ->get();

        return view('items.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('items.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:150'],
                'category_id' => ['required', 'exists:categories,id'],
                'stock' => ['required', 'integer', 'min:0'],
            ],
            [
                'name.required' => 'The name field is required.',
                'category_id.required' => 'The category field is required.',
                'category_id.exists' => 'Category not found.',
                'stock.required' => 'The total field is required.',
                'stock.integer' => 'The total must be a number.',
                'stock.min' => 'The total must be at least 0.',
            ]
        );

        Item::create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'stock' => $validated['stock'],
            'repair_count' => 0,
        ]);

        return redirect()
            ->route('items.index')
            ->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('items.edit', compact('item', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:150'],
                'category_id' => ['required', 'exists:categories,id'],
                'stock' => ['required', 'integer', 'min:0'],

                // input “new broke item”
                'new_repair' => ['nullable', 'integer', 'min:0'],
            ],
            [
                'name.required' => 'The name field is required.',
                'category_id.required' => 'The category field is required.',
                'category_id.exists' => 'Category not found.',
                'stock.required' => 'The total field is required.',
                'stock.integer' => 'The total must be a number.',
                'stock.min' => 'The total must be at least 0.',
                'new_repair.integer' => 'New broke item must be a number.',
                'new_repair.min' => 'New broke item must be at least 0.',
            ]
        );

        $newRepair = (int) ($validated['new_repair'] ?? 0);

        $item->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'stock' => $validated['stock'],
            'repair_count' => $item->repair_count + $newRepair,
        ]);

        return redirect()
            ->route('items.index')
            ->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()
            ->route('items.index')
            ->with('success', 'Item deleted successfully.');
    }

    public function exportExcel()
    {
        return Excel::download(new ItemsExport, 'items.xlsx');
    }
}
