<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminAccountsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return User::query()
            ->where('role', 'admin')
            ->orderBy('id')
            ->get(['id', 'name', 'email', 'password']);
    }

    public function headings(): array
    {
        return ['Name', 'Email', 'Password'];
    }

    public function map($user): array
    {
        // default password rule: 4 karakter awal email + id
        $defaultPassword = substr($user->email, 0, 4) . $user->id;

        // kalau hash di database cocok dengan default password => tampilkan default password
        // kalau tidak cocok => anggap sudah diganti => jangan tampilkan password
        $passwordCell = Hash::check($defaultPassword, $user->password)
            ? $defaultPassword
            : 'This account already edited the password';

        return [
            $user->name,
            $user->email,
            $passwordCell,
        ];
    }
}
