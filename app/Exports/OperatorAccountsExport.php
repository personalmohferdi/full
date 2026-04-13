<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OperatorAccountsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return User::query()
            ->where('role', 'operator')
            ->orderBy('id')
            ->get(['id', 'name', 'email', 'password']);
    }

    public function headings(): array
    {
        return ['Name', 'Email', 'Password'];
    }

    public function map($user): array
    {
        // default password: 4 karakter awal email + id user
        $defaultPassword = substr($user->email, 0, 4) . $user->id;

        // kalau masih default => tampilkan default password
        // kalau sudah diganti => jangan tampilkan password
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
