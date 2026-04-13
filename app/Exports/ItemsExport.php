<?php

namespace App\Exports;

use App\Models\Item;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return Item::query()
            ->with('category')
            ->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Category',
            'Name Item',
            'Total',
            'Repair Total',
            'Last Updated',
        ];
    }

    public function map($item): array
    {
        return [
            $item->category?->name ?? '-',
            $item->name,
            $item->stock,
            $item->repair_count == 0 ? '-' : $item->repair_count,
            $item->updated_at?->format('M d, Y'), // contoh: Jan 14, 2023
        ];
    }
}