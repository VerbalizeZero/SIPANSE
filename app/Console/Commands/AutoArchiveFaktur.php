<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TuFaktur;
use Carbon\Carbon;

class AutoArchiveFaktur extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'faktur:auto-archive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengeksekusi otomatisasi arsip faktur jika umurnya telah lebih dari 7 hari sejak ditetapkan Selesai';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Tetapkan limit waktu batas mundur 7 Hari
        $batasWaktu = Carbon::now('Asia/Jakarta')->subDays(7);

        // Cari faktur yang sudah "Selesai" (diekspor oleh TU dan siap arsip)
        // Tetapi belum menjadi arsip, dan sudah mengendap tak berubah selama lebih dari 7 hari
        $fakturLewatBatas = TuFaktur::where('status', 'selesai')
            ->where('updated_at', '<=', $batasWaktu)
            ->get();

        $jumlahDiarsipkan = 0;
        foreach ($fakturLewatBatas as $faktur) {
            $faktur->update(['status' => 'diarsipkan']);
            $jumlahDiarsipkan++;
        }

        $this->info("Berhasil mengarsipkan {$jumlahDiarsipkan} faktur yang telah melewati masa tenggang 7 hari.");
    }
}
