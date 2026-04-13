<?php

namespace App\Http\Controllers;

use App\Exports\LendingsExport;
use App\Models\Item;
use App\Models\Lending;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LendingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Lending::query()
            ->with(['item', 'user'])
            ->orderBy('date', 'desc');

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id)->where('status', 'borrowed');
        }

        $lendings = $query->get();

        // flag untuk view: sedang mode detail per item atau tidak
        $isDetail = $request->filled('item_id');

        return view('lendings.index', compact('lendings', 'isDetail'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $items = Item::query()
            ->with('lendings') // supaya lending_total/available bisa dihitung
            ->orderBy('name')
            ->get();

        return view('lendings.create', compact('items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'borrower_name' => ['required', 'string', 'max:150'],
                'date' => ['nullable', 'date'],
                'description' => ['nullable', 'string'],

                'items' => ['required', 'array', 'min:1'],
                'items.*.item_id' => ['required', 'exists:items,id'],
                'items.*.qty' => ['required', 'integer', 'min:1'],
            ],
            [
                'borrower_name.required' => 'The borrower name field is required.',
                'items.required' => 'The item selection is required.',
                'items.*.item_id.required' => 'The item selection is required.',
                'items.*.qty.required' => 'The total field is required.',
                'items.*.qty.min' => 'The total must be at least 1.',
            ]
        );

        // Gabungkan kalau item yang sama dipilih beberapa kali
        $grouped = collect($validated['items'])
            ->groupBy('item_id')
            ->map(fn($rows) => $rows->sum('qty'));

        $items = Item::query()
            ->whereIn('id', $grouped->keys())
            ->with('lendings')
            ->get()
            ->keyBy('id');

        foreach ($grouped as $itemId => $totalQty) {
            $item = $items[$itemId];

            // RULE: jika qty > available -> error
            if ($totalQty > $item->available) {
                return back()
                    ->withInput()
                    ->with('error', 'total item more than available');
            }
        }

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $row) {
                Lending::create([
                    'item_id' => $row['item_id'],
                    'user_id' => auth()->id(),
                    'borrower_name' => $validated['borrower_name'],
                    'qty' => $row['qty'],
                    'description' => $validated['description'] ?? null,
                    'date' => $validated['date'] ?? now()->toDateString(),
                    'return_date' => null,
                    'status' => 'borrowed',
                ]);
            }
        });

        return redirect()
            ->route('lendings')
            ->with('success', 'Lending created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lending $lending)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lending $lending)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lending $lending)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lending $lending)
    {
        $wasBorrowed = ($lending->status === 'borrowed' && $lending->return_date === null);

        $lending->delete();

        // Kalau belum returned, available otomatis "balik" karena lending_total berkurang.
        $message = $wasBorrowed
            ? 'Lending deleted. Item available has been restored.'
            : 'Lending deleted successfully.';

        return redirect()
            ->route('lendings')
            ->with('success', $message);
    }

    public function returned(Lending $lending)
    {
        // sudah returned -> tidak usah diproses lagi
        if ($lending->status === 'returned') {
            return back()->with('error', 'This lending is already returned.');
        }

        $lending->status = 'returned';
        $lending->return_date = now()->toDateString();
        $lending->user_id = auth()->id();
        $lending->save();

        return redirect()
            ->route('lendings')
            ->with('success', 'Item successfully returned.');
    }

    public function exportExcel()
    {
        return Excel::download(new LendingsExport, 'lendings.xlsx');
    }
}
