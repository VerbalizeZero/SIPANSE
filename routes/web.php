<?php

use App\Http\Controllers\Bendahara\MasterFakturController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tu\DataKelasController;
use App\Http\Controllers\Tu\FakturController;
use App\Http\Controllers\Tu\SiswaImportExportController;
use App\Http\Controllers\Tu\VerifikasiController;
use App\Http\Controllers\Ortu\DashboardController as OrtuDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
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

    Route::middleware(['auth', 'role:tu'])->group(function () {
        // Iterasi-04: Data Kelas (ambil basis data dari siswa, lalu kelola metadata kelas).
        Route::get('/tu/kelas', [DataKelasController::class, 'index'])
            ->name('tu.kelas.index');
        Route::post('/tu/kelas', [DataKelasController::class, 'store'])
            ->name('tu.kelas.store');
        Route::put('/tu/kelas/{dataKela}', [DataKelasController::class, 'update'])
            ->name('tu.kelas.update');
        Route::delete('/tu/kelas/{dataKela}', [DataKelasController::class, 'destroy'])
            ->name('tu.kelas.destroy');
        // Endpoint preview dan eksekusi promote massal level kelas.
        Route::post('/tu/kelas/promote/preview', [DataKelasController::class, 'previewPromote'])
            ->name('tu.kelas.promote.preview');
        Route::post('/tu/kelas/promote/execute', [DataKelasController::class, 'executePromote'])
            ->name('tu.kelas.promote.execute');

        // Iterasi-03: Data Siswa (Mengambil data dari template export & inmport)
        Route::get('/tu/siswa', [SiswaImportExportController::class, 'index'])
            ->name('tu.siswa.index');
        Route::get('/tu/siswa/template', [SiswaImportExportController::class, 'downloadTemplate'])
            ->name('tu.siswa.template');
        Route::post('/tu/siswa/import', [SiswaImportExportController::class, 'import'])
            ->name('tu.siswa.import');
        Route::put('/tu/siswa/{siswa}', [SiswaImportExportController::class, 'update'])
            ->name('tu.siswa.update');
        Route::delete('/tu/siswa/{siswa}', [SiswaImportExportController::class, 'destroy'])
            ->name('tu.siswa.destroy');

        // Iterasi-05: Menu Faktur untuk Tata Usaha.
        Route::get('/tu/faktur', [FakturController::class, 'index'])
            ->name('tu.faktur.index');
        Route::post('/tu/faktur', [FakturController::class, 'store'])
            ->name('tu.faktur.store');
        Route::put('/tu/faktur/{faktur}', [FakturController::class, 'update'])
            ->name('tu.faktur.update');
        Route::delete('/tu/faktur/{faktur}', [FakturController::class, 'destroy'])
            ->name('tu.faktur.destroy');

        // Iterasi-06: Verifikasi pembayaran faktur.
        Route::get('/tu/verifikasi', [VerifikasiController::class, 'index'])
            ->name('tu.verifikasi.index');
        Route::get('/tu/verifikasi/{faktur}', [VerifikasiController::class, 'show'])
            ->name('tu.verifikasi.show');
        Route::post('/tu/verifikasi/{faktur}/reject', [VerifikasiController::class, 'reject'])
            ->name('tu.verifikasi.reject');
        Route::post('/tu/verifikasi/{faktur}/export', [VerifikasiController::class, 'export'])
            ->name('tu.verifikasi.export');
        Route::post('/tu/verifikasi/{faktur}/siswa/{siswa}/status', [VerifikasiController::class, 'updateStatusSiswa'])
            ->name('tu.verifikasi.update_status_siswa');
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

    Route::post('/ortu/logout', [\App\Http\Controllers\Ortu\AuthController::class, 'logout'])
         ->name('ortu.logout');

    // Route khusus Orang Tua
    Route::middleware(['auth', 'role:orang_tua'])->group(function () {
        Route::get('/ortu', [OrtuDashboardController::class, 'index'])->name('ortu.dashboard');

        Route::get('/ortu/faktur', [\App\Http\Controllers\Ortu\FakturController::class, 'index'])
             ->name('ortu.faktur.index');
        Route::post('/ortu/faktur/{faktur}/submit', [\App\Http\Controllers\Ortu\FakturController::class, 'submit'])
             ->name('ortu.faktur.submit');
    });
});

// Route login Orang Tua harus di luar middleware 'auth' agar bisa diakses tanpa sesi aktif
Route::get('/ortu/login', [\App\Http\Controllers\Ortu\AuthController::class, 'showLoginForm'])
     ->name('ortu.login.form');
Route::post('/ortu/login', [\App\Http\Controllers\Ortu\AuthController::class, 'login'])
     ->name('ortu.login.submit');

require __DIR__.'/auth.php';
