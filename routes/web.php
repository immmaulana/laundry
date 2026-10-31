<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemLineController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Landing');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('setting', [PageController::class, 'settingEdit'])->name('setting.edit');
    Route::post('setting', [PageController::class, 'settingUpdate'])->name('setting.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Platform owner: daftar & buat PT
    Route::middleware('role:super_admin')->group(function () {
        Route::get('companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('companies/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::post('companies', [CompanyController::class, 'store'])->name('companies.store');
        Route::delete('companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    });

    // Super admin atau admin PT: edit perusahaan
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::put('companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    });

    // Shortcut profil PT untuk admin perusahaan
    Route::get('company/profile', function () {
        abort_unless(auth()->user()->isAdmin() && auth()->user()->company, 403);

        return redirect()->route('companies.edit', auth()->user()->company);
    })->name('company.profile');

    // Operasional tenant
    Route::middleware('role:admin,kasir')->group(function () {
        Route::get('kasir', [KasirController::class, 'index'])->name('kasir.index');
        Route::post('kasir', [KasirController::class, 'store'])->name('kasir.store');

        Route::get('antrian', [QueueController::class, 'index'])->name('queue.index');
        Route::patch('antrian/{order}/status', [QueueController::class, 'updateStatus'])->name('queue.status');

        Route::resource('order', OrderController::class)->except(['show']);
        Route::get('order/{order}/receipt', [OrderController::class, 'receipt'])->name('order.receipt');
        Route::post('order/{order}/item-line', [OrderItemLineController::class, 'store'])->name('order.item.store');
        Route::delete('order/{order}/item-line/{item}', [OrderItemLineController::class, 'destroy'])->name('order.item.destroy');

        Route::resource('customer', CustomerController::class)->except(['show']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('plan', PlanController::class)->except(['show']);
        Route::get('report', [ReportController::class, 'index'])->name('report.index');
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
    });
});

require __DIR__.'/auth.php';
