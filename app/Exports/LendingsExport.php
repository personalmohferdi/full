<?php

namespace App\Exports;

use App\Models\Lending;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LendingsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return Lending::query()
            ->with(['item', 'user'])
            ->orderBy('date', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return ['Item', 'Total', 'Name', 'Ket.', 'Date', 'Return Date', 'Edited By'];
    }

    public function map($lending): array
    {
        // Format tanggal seperti contoh: Jan 14, 2023
        $date = $lending->date ? date('M d, Y', strtotime($lending->date)) : '-';

        // Kalau belum dikembalikan => "-"
        $returnDate = $lending->return_date
            ? date('M d, Y', strtotime($lending->return_date))
            : '-';

        return [
            $lending->item?->name ?? '-',
            $lending->qty,
            // NOTE: ini butuh kolom borrower_name di table lendings.
            // Kalau belum ada, ganti jadi '-' atau pakai field lain yang kamu punya.
            $lending->borrower_name ?? '-',
            $lending->description ?? '-',
            $date,
            $returnDate,
            $lending->user?->name ?? '-',
        ];
    }
}
