<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Artisan::command('faktur:auto-archive', function () {
    $this->call(\App\Console\Commands\AutoArchiveFaktur::class);
})->purpose('Mengeksekusi arsip faktur 7-Hari secara otomatis')
  ->dailyAt('00:00');
