<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LendingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::middleware('guest')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    // ========== ADMIN ONLY ==========
    Route::middleware('role:admin')->group(function () {

        // CATEGORIES (admin saja)
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
            Route::get('/create', [CategoryController::class, 'create'])->name('categories.create');
            Route::post('/', [CategoryController::class, 'store'])->name('categories.store');
            Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        });

        // ITEMS: create/edit/delete/export (admin saja)
        Route::prefix('items')->group(function () {
            Route::get('/create', [ItemController::class, 'create'])->name('items.create');
            Route::post('/', [ItemController::class, 'store'])->name('items.store');
            Route::get('/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
            Route::put('/{item}', [ItemController::class, 'update'])->name('items.update');
            Route::delete('/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
            Route::get('/export/excel', [ItemController::class, 'exportExcel'])->name('items.export.excel');
        });

        // USERS (admin saja)
        Route::prefix('users')->group(function () {
            Route::get('/admin', [UserController::class, 'adminsIndex'])->name('users.admin');
            Route::get('/admin/export/excel', [UserController::class, 'exportAdminsExcel'])->name('users.admin.export.excel');

            Route::get('/operator', [UserController::class, 'operatorsIndex'])->name('users.operator');
            Route::get('/operator/export/excel', [UserController::class, 'exportOperatorsExcel'])->name('users.operator.export.excel');

            Route::get('/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::put('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        });

        // LENDINGS: returned & delete (admin saja)
        Route::prefix('lendings')->group(function () {
            Route::put('/{lending}/returned', [LendingController::class, 'returned'])->name('lendings.returned');
            Route::delete('/{lending}', [LendingController::class, 'destroy'])->name('lendings.destroy');
        });
    });

    Route::middleware('role:operator')->group(function () {

        Route::prefix('lendings')->group(function () {
            Route::get('/create', [LendingController::class, 'create'])->name('lendings.create');
            Route::post('/', [LendingController::class, 'store'])->name('lendings.store');
            Route::get('/export/excel', [LendingController::class, 'exportExcel'])->name('lendings.export.excel');
        });
        
    });

    // ========== ADMIN + OPERATOR ==========
    Route::middleware('role:admin,operator')->group(function () {

        // ITEMS index boleh dua role
        Route::get('/items', [ItemController::class, 'index'])->name('items.index');
        Route::get('/lendings', [LendingController::class, 'index'])->name('lendings');

        Route::prefix('users')->group(function () {
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        });

    });
});
