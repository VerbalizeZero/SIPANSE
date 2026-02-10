<?php

use App\Http\Controllers\Bendahara\MasterFakturController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['auth', 'role:tu'])->get('/tu', function () {
        return view('roles.tu');
    })->name('tu.dashboard');
    Route::middleware(['auth', 'role:bendahara'])->get('/bendahara', function () {
        return view('roles.bendahara');
    })->name('bendahara.dashboard');
    Route::middleware(['auth', 'role:bendahara'])->group(function () {
        Route::get('/bendahara/master-faktur', [MasterFakturController::class, 'index'])
            ->name('bendahara.master-faktur.index');
        Route::post('/bendahara/master-faktur', [MasterFakturController::class, 'store'])
            ->name('bendahara.master-faktur.store');
        Route::put('/bendahara/master-faktur/{masterFaktur}', [MasterFakturController::class, 'update'])
            ->name('bendahara.master-faktur.update');
        Route::delete('/bendahara/master-faktur/{masterFaktur}', [MasterFakturController::class, 'destroy'])
            ->name('bendahara.master-faktur.destroy');
    });
    Route::middleware(['auth', 'role:ortu'])->get('/ortu', function () {
        return view('roles.ortu');
    })->name('ortu.dashboard');
});

require __DIR__.'/auth.php';
