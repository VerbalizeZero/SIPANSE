<?php

namespace Tests\Feature\Arsip;

use App\Models\MasterFaktur;
use App\Models\TuFaktur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TuAutoArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_archive_command_only_archives_fakturs_older_than_7_days()
    {
        $user = \App\Models\User::factory()->create(['role' => 'tu']);

        $masterFaktur = MasterFaktur::create([
            'nama_faktur' => 'Uang Komputer',
            'nominal' => 200000,
        ]);

        // Faktur 1: Selesai HARI INI (Umur 0 hari)
        $fakturBaru = TuFaktur::create([
            'master_faktur_id' => $masterFaktur->id,
            'target_type' => 'angkatan',
            'target_value' => '2026',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'selesai',
            'created_by' => $user->id,
        ]);
        
        // Manipulasi tanggal updated_at faktur agar seolah terjadi hari ini
        $fakturBaru->updated_at = Carbon::now('Asia/Jakarta');
        $fakturBaru->save();

        // Faktur 2: Selesai 8 HARI LALU (Telah mencapai syarat timeout karantina)
        $fakturKadaluarsa = TuFaktur::create([
            'master_faktur_id' => $masterFaktur->id,
            'target_type' => 'angkatan',
            'target_value' => '2027',
            'tersedia_pada' => now()->subDays(15)->toDateString(),
            'jatuh_tempo' => now()->subDays(8)->toDateString(),
            'status' => 'selesai',
            'created_by' => $user->id,
        ]);

        // Manipulasi tanggal agar sistem mengira ini sudah dianggurkan selama 8 hari
        $fakturKadaluarsa->updated_at = Carbon::now('Asia/Jakarta')->subDays(8);
        $fakturKadaluarsa->save();

        // 3. Eksekusi Robot Auto Archive
        $this->artisan('faktur:auto-archive')
            ->expectsOutputToContain('Berhasil mengarsipkan 1 faktur')
            ->assertExitCode(0);

        // 4. Assert Database Changes
        // Faktur baru masih Selesai dan tidak pindah ke arsip
        $this->assertDatabaseHas('tu_fakturs', [
            'id' => $fakturBaru->id,
            'status' => 'selesai'
        ]);

        // Faktur kadaluarsa sudah pindah status menjadi diarsipkan
        $this->assertDatabaseHas('tu_fakturs', [
            'id' => $fakturKadaluarsa->id,
            'status' => 'diarsipkan'
        ]);
    }
}
