<?php

use App\Http\Controllers\Bendahara\MasterFakturController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tu\SiswaImportExportController;
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
    // Iterasi 03: seluruh fitur Data Siswa untuk role TU.
    Route::middleware(['auth', 'role:tu'])->group(function () {
        // Halaman utama daftar siswa (read/list).
        Route::get('/tu/siswa', [SiswaImportExportController::class, 'index'])
            ->name('tu.siswa.index');
        // Download header template CSV berdasarkan kolom tabel siswas.
        Route::get('/tu/siswa/template', [SiswaImportExportController::class, 'downloadTemplate'])
            ->name('tu.siswa.template');
        // Import data siswa dari file CSV/TXT.
        Route::post('/tu/siswa/import', [SiswaImportExportController::class, 'import'])
            ->name('tu.siswa.import');
        // Update satu siswa via route-model binding {siswa}.
        Route::put('/tu/siswa/{siswa}', [SiswaImportExportController::class, 'update'])
            ->name('tu.siswa.update');
        // Hapus satu siswa via route-model binding {siswa}.
        Route::delete('/tu/siswa/{siswa}', [SiswaImportExportController::class, 'destroy'])
            ->name('tu.siswa.destroy');
    });
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
