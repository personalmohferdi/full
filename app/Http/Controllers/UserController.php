<?php

namespace App\Http\Controllers;

use App\Exports\AdminAccountsExport;
use App\Exports\OperatorAccountsExport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function adminsIndex()
    {
        $users = User::query()
            ->where('role', 'admin')
            ->orderBy('id', 'asc')
            ->get();

        return view('users.admin', compact('users'));
    }

    public function operatorsIndex()
    {
        $users = User::query()
            ->where('role', 'operator')
            ->orderBy('id', 'asc')
            ->get();

        return view('users.operator', compact('users'));
        // sesuaikan nama blade jika file kamu beda, mis. users/operators/index
    }

    public function create()
    {
        return view('users.create'); // sesuaikan path blade kamu
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:150'],
                'email' => ['required', 'email', 'max:150', 'unique:users,email'],
                'role' => ['required', 'in:admin,operator'],
            ],
            [
                'name.required' => 'The name field is required.',
                'email.required' => 'The email field is required.',
                'email.email' => 'The email must be a valid email address.',
                'email.unique' => 'The email has already been taken.',
                'role.required' => 'The role selection is required.',
                'role.in' => 'Role must be admin or operator.',
            ]
        );

        // 1) Buat user dulu supaya dapat ID
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],

            // placeholder sementara (wajib kalau kolom password NOT NULL)
            'password' => Hash::make('temp-password'),
        ]);

        // 2) Password final: 4 karakter awal email + ID user
        $prefix = substr($validated['email'], 0, 4);
        $plainPassword = $prefix . $user->id;

        $user->password = Hash::make($plainPassword);
        $user->save();

        // redirect sesuai role agar enak
        $redirectRoute = $user->role === 'admin'
            ? 'users.admin'
            : 'users.operator'; // kalau belum ada, ganti sementara ke users.admin

        return redirect()
            ->route($redirectRoute)
            ->with('success', "Account created successfully. Password: {$plainPassword}");
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user')); // sesuaikan path blade
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:150'],
                'email' => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
                'new_password' => ['nullable', 'string', 'min:8'],
            ],
            [
                'name.required' => 'The name field is required.',
                'email.required' => 'The email field is required.',
                'email.email' => 'The email must be a valid email address.',
                'email.unique' => 'The email has already been taken.',
                'new_password.min' => 'The new password must be at least 8 characters.',
            ]
        );

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // password OPTIONAL: kalau kosong, jangan ubah
        if (!empty($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        // redirect balik ke halaman sesuai role user yang diedit
        if ($user->role === 'admin') {
            return redirect()
                ->route('users.admin')
                ->with('success', 'Account updated successfully.');
        }

        // operator -> kembali ke halaman edit user tsb (butuh parameter)
        return redirect()
            ->route('users.edit', $user->id)   // atau ['user' => $user->id]
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(User $user)
    {
        // tidak boleh hapus akun yang sedang login
        if (Auth::id() === $user->id) {
            // return back()->with('success', ''); // optional kalau kamu tidak mau success
            // lebih bagus pakai error message:
            return back()->with('error', 'Tidak dapat menghapus akun yang sedang dipakai.');
        }

        $user->delete();

        return redirect()
            ->route('users.admin') // atau sesuaikan role
            ->with('success', 'Account deleted successfully.');
    }

    public function exportAdminsExcel()
    {
        return Excel::download(new AdminAccountsExport, 'admin-accounts.xlsx');
    }

    public function exportOperatorsExcel()
    {
        return Excel::download(new OperatorAccountsExport, 'operator-accounts.xlsx');
    }

    public function resetPassword(User $user)
    {
        // optional: tidak boleh reset akun yang sedang login
        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot reset password for the currently logged-in account.');
        }

        $plainPassword = substr($user->email, 0, 4) . $user->id;

        $user->password = Hash::make($plainPassword);
        $user->save();

        // balik ke halaman sebelumnya (admin/operator)
        return back()->with('success', "Password reset successfully. New password: {$plainPassword}");
    }
}
