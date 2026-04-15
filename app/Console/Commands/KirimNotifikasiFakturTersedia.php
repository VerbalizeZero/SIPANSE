<?php

namespace App\Console\Commands;

use App\Models\TuFaktur;
use App\Services\NotifikasiService;
use Illuminate\Console\Command;

class KirimNotifikasiFakturTersedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifikasi:kirim-faktur-tersedia';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi ke ortu untuk faktur yang tersedia hari ini dan belum pernah dikirim';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fakturs = TuFaktur::whereDate('tersedia_pada', '<=', now())
            ->whereNull('notifikasi_dikirim_at')
            ->get();

        $count = 0;
        foreach ($fakturs as $faktur) {
            NotifikasiService::notifyOrtuForNewFaktur($faktur);
            $faktur->update(['notifikasi_dikirim_at' => now()]);
            $count++;
        }

        $this->info("{$count} faktur diproses dan notifikasi dikirim.");

        return self::SUCCESS;
    }
}
