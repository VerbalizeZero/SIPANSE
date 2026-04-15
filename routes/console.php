<?php

use App\Services\NotifikasiService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('faktur:auto-archive', function () {
    $this->call(\App\Console\Commands\AutoArchiveFaktur::class);
})->purpose('Mengeksekusi arsip faktur 7-Hari secara otomatis')
  ->dailyAt('00:00');

// Backup database harian via spatie/laravel-backup (03:00 WIB)
Schedule::command('backup:run --only-db')->dailyAt('03:00');

// Cleanup backup lama (simpan 7 hari terakhir) (03:30 WIB)
Schedule::command('backup:clean')->dailyAt('03:30');

// Kirim notifikasi faktur tersedia ke ortu setiap pagi (06:00 WIB)
Schedule::command('notifikasi:kirim-faktur-tersedia')->dailyAt('06:00');

// Hapus notifikasi ortu yang sudah lebih dari 3 hari (06:30 WIB)
Schedule::call(function () {
    $deleted = NotifikasiService::pruneOldNotifications(3);
    info("Notifikasi lama dihapus: {$deleted} records");
})->dailyAt('06:30');

// Maintenance terjadwal: down jam 21:00, up jam 04:00 WIB
Schedule::command('down --render="errors::503"')->dailyAt('21:00');
Schedule::command('up')->dailyAt('04:00');
