<?php

namespace Tests\Feature\Ortu;

use App\Models\MasterFaktur;
use App\Models\PenyerahanFaktur;
use App\Models\Siswa;
use App\Models\TuFaktur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrtuFakturWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function createMaster(string $nama, int $nominal = 250000): MasterFaktur
    {
        return MasterFaktur::create([
            'jenis_faktur' => 'SPP',
            'nama_faktur' => $nama,
            'nominal' => $nominal,
            'deskripsi' => null,
        ]);
    }

    private function createOrtuFromSiswa(array $override = []): array
    {
        $siswa = Siswa::create(array_merge([
            'nisn' => '1202988374',
            'nama_siswa' => 'Dober Mejiro',
            'tahun_angkatan' => '2027',
            'kelas' => 'D',
            'jenis_kelamin' => 'Laki-laki',
        ], $override));

        $ortu = User::where('nisn', $siswa->nisn)->firstOrFail();

        return [$siswa, $ortu];
    }

    /** @test */
    public function orang_tua_can_open_faktur_page(): void
    {
        [, $ortu] = $this->createOrtuFromSiswa();

        $this->actingAs($ortu)
            ->get('/ortu/faktur')
            ->assertOk();
    }

    /** @test */
    public function orang_tua_only_sees_faktur_that_match_their_target(): void
    {
        [$siswa, $ortu] = $this->createOrtuFromSiswa();

        $masterAll = $this->createMaster('SPP Semua');
        $masterAngkatan = $this->createMaster('SPP Angkatan');
        $masterKelas = $this->createMaster('SPP Kelas');
        $masterPersonal = $this->createMaster('SPP Personal');
        $masterOther = $this->createMaster('SPP Bukan Target');
        $masterClosed = $this->createMaster('SPP Ditutup');

        TuFaktur::create([
            'master_faktur_id' => $masterAll->id,
            'target_type' => 'semua',
            'target_value' => null,
            'tersedia_pada' => '2026-04-01',
            'jatuh_tempo' => '2026-04-30',
            'status' => 'Berjalan',
        ]);

        TuFaktur::create([
            'master_faktur_id' => $masterAngkatan->id,
            'target_type' => 'angkatan',
            'target_value' => $siswa->tahun_angkatan,
            'tersedia_pada' => '2026-04-01',
            'jatuh_tempo' => '2026-04-30',
            'status' => 'Berjalan',
        ]);

        TuFaktur::create([
            'master_faktur_id' => $masterKelas->id,
            'target_type' => 'kelas',
            'target_value' => $siswa->kelas,
            'tersedia_pada' => '2026-04-01',
            'jatuh_tempo' => '2026-04-30',
            'status' => 'Berjalan',
        ]);

        TuFaktur::create([
            'master_faktur_id' => $masterPersonal->id,
            'target_type' => 'siswa',
            'target_value' => "{$siswa->nama_siswa} - {$siswa->nisn}",
            'tersedia_pada' => '2026-04-01',
            'jatuh_tempo' => '2026-04-30',
            'status' => 'Berjalan',
        ]);

        TuFaktur::create([
            'master_faktur_id' => $masterOther->id,
            'target_type' => 'angkatan',
            'target_value' => '2028',
            'tersedia_pada' => '2026-04-01',
            'jatuh_tempo' => '2026-04-30',
            'status' => 'Berjalan',
        ]);

        TuFaktur::create([
            'master_faktur_id' => $masterClosed->id,
            'target_type' => 'angkatan',
            'target_value' => $siswa->tahun_angkatan,
            'tersedia_pada' => '2026-04-01',
            'jatuh_tempo' => '2026-04-30',
            'status' => 'Selesai',
        ]);

        $this->actingAs($ortu)
            ->get('/ortu/faktur')
            ->assertOk()
            ->assertSee('SPP Semua')
            ->assertSee('SPP Angkatan')
            ->assertSee('SPP Kelas')
            ->assertSee('SPP Personal')
            ->assertDontSee('SPP Bukan Target')
            ->assertDontSee('SPP Ditutup');
    }

    /** @test */
    public function orang_tua_can_resubmit_file_and_reset_previous_rejection_status(): void
    {
        Storage::fake('public');
        [$siswa, $ortu] = $this->createOrtuFromSiswa();

        $master = $this->createMaster('SPP Ulang');
        $faktur = TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'siswa',
            'target_value' => "{$siswa->nama_siswa} - {$siswa->nisn}",
            'tersedia_pada' => '2026-04-01',
            'jatuh_tempo' => '2026-04-30',
            'status' => 'Berjalan',
        ]);

        PenyerahanFaktur::create([
            'tu_faktur_id' => $faktur->id,
            'siswa_id' => $siswa->id,
            'berkas_file' => 'penyerahan_faktur/lama.jpg',
            'status' => 'ditolak',
            'catatan_penolakan' => 'Bukti tidak jelas',
        ]);

        $newFile = UploadedFile::fake()->image('baru.jpg');

        $this->actingAs($ortu)
            ->post("/ortu/faktur/{$faktur->id}/submit", [
                'berkas_file' => $newFile,
            ])
            ->assertRedirect();

        $record = PenyerahanFaktur::where('tu_faktur_id', $faktur->id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        $this->assertSame('menunggu_verifikasi', $record->status);
        $this->assertNull($record->catatan_penolakan);
        Storage::disk('public')->assertExists($record->berkas_file);
    }

    /** @test */
    public function non_orang_tua_role_cannot_access_ortu_faktur_page(): void
    {
        $tu = User::factory()->tu()->create();

        $this->actingAs($tu)
            ->get('/ortu/faktur')
            ->assertForbidden();
    }
}

