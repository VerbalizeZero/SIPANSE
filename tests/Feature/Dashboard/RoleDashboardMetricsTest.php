<?php

namespace Tests\Feature\Dashboard;

use App\Models\MasterFaktur;
use App\Models\PenyerahanFaktur;
use App\Models\Siswa;
use App\Models\TuFaktur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_tu_dashboard_shows_summary_metrics(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $tuLain = User::factory()->create(['role' => 'tu']);

        $master = MasterFaktur::create([
            'nama_faktur' => 'SPP Dashboard TU',
            'nominal' => 200000,
        ]);

        $fakturPending = TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'semua_siswa',
            'target_value' => 'Semua Siswa',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status' => 'pending',
            'created_by' => $tu->id,
        ]);
        $fakturSelesai = TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'kelas',
            'target_value' => 'X-A',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status' => 'selesai',
            'created_by' => $tu->id,
        ]);
        TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'angkatan',
            'target_value' => '2026',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status' => 'diarsipkan',
            'created_by' => $tuLain->id,
        ]);

        $siswaA = Siswa::create([
            'nisn' => '1200000001',
            'nama_siswa' => 'Siswa A',
            'tahun_angkatan' => '2026',
            'jenis_kelamin' => 'L',
            'alamat' => 'A',
            'nama_ortu' => 'Ortu A',
            'no_hp_ortu' => '0811',
            'kelas' => 'X-A',
        ]);
        $siswaB = Siswa::create([
            'nisn' => '1200000002',
            'nama_siswa' => 'Siswa B',
            'tahun_angkatan' => '2026',
            'jenis_kelamin' => 'P',
            'alamat' => 'B',
            'nama_ortu' => 'Ortu B',
            'no_hp_ortu' => '0812',
            'kelas' => 'X-A',
        ]);

        PenyerahanFaktur::create([
            'tu_faktur_id' => $fakturPending->id,
            'siswa_id' => $siswaA->id,
            'berkas_file' => 'penyerahan_faktur/a.jpg',
            'status' => 'diverifikasi',
            'verified_by' => $tu->id,
            'verified_at' => now('Asia/Jakarta'),
        ]);
        PenyerahanFaktur::create([
            'tu_faktur_id' => $fakturSelesai->id,
            'siswa_id' => $siswaB->id,
            'berkas_file' => 'penyerahan_faktur/b.jpg',
            'status' => 'ditolak',
            'catatan_penolakan' => 'Nominal belum sesuai',
            'verified_by' => $tu->id,
            'verified_at' => now('Asia/Jakarta'),
        ]);

        $response = $this->actingAs($tu)->get(route('tu.dashboard'));

        $response->assertOk()
            ->assertViewIs('roles.tu')
            ->assertViewHas('stats', function (array $stats) {
                return ($stats['total_faktur_dibuat'] ?? null) === 2
                    && ($stats['total_faktur_berjalan'] ?? null) === 1
                    && ($stats['total_faktur_selesai'] ?? null) === 1
                    && ($stats['total_diterima'] ?? null) === 1
                    && ($stats['total_ditolak'] ?? null) === 1;
            });
    }

    public function test_bendahara_dashboard_shows_summary_metrics(): void
    {
        $bendahara = User::factory()->create(['role' => 'bendahara']);
        $tu = User::factory()->create(['role' => 'tu']);

        MasterFaktur::create([
            'nama_faktur' => 'SPP',
            'nominal' => 250000,
        ]);
        MasterFaktur::create([
            'nama_faktur' => 'Ujian',
            'nominal' => 175000,
        ]);

        $master = MasterFaktur::first();

        TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'semua_siswa',
            'target_value' => 'Semua Siswa',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status' => 'pending',
            'created_by' => $tu->id,
        ]);
        TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'kelas',
            'target_value' => 'X-A',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status' => 'selesai',
            'created_by' => $tu->id,
        ]);
        TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'kelas',
            'target_value' => 'X-B',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status' => 'diarsipkan',
            'created_by' => $tu->id,
        ]);

        $response = $this->actingAs($bendahara)->get(route('bendahara.dashboard'));

        $response->assertOk()
            ->assertViewIs('roles.bendahara')
            ->assertViewHas('stats', function (array $stats) {
                return ($stats['total_master_faktur'] ?? null) === 2
                    && ($stats['total_faktur_berjalan'] ?? null) === 1
                    && ($stats['total_faktur_selesai'] ?? null) === 2;
            });
    }

    public function test_ortu_dashboard_shows_summary_metrics(): void
    {
        $ortu = User::factory()->create([
            'role' => 'orang_tua',
            'nisn' => '1200000010',
        ]);
        $tu = User::factory()->create(['role' => 'tu']);

        $siswa = Siswa::create([
            'nisn' => '1200000010',
            'nama_siswa' => 'Anak Ortu',
            'tahun_angkatan' => '2027',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Test',
            'nama_ortu' => 'Nama Ortu',
            'no_hp_ortu' => '08123',
            'kelas' => 'D',
        ]);

        $master = MasterFaktur::create([
            'nama_faktur' => 'SPP Ortu',
            'nominal' => 100000,
        ]);

        $fakturA = TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'semua_siswa',
            'target_value' => 'Semua Siswa',
            'tersedia_pada' => now()->subDay()->toDateString(),
            'jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status' => 'pending',
            'created_by' => $tu->id,
        ]);
        $fakturB = TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'kelas',
            'target_value' => 'D',
            'tersedia_pada' => now()->subDay()->toDateString(),
            'jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status' => 'pending',
            'created_by' => $tu->id,
        ]);
        $fakturC = TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'angkatan',
            'target_value' => '2027',
            'tersedia_pada' => now()->subDay()->toDateString(),
            'jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status' => 'pending',
            'created_by' => $tu->id,
        ]);
        $fakturD = TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'siswa',
            'target_value' => 'Anak Ortu - 1200000010',
            'tersedia_pada' => now()->subDay()->toDateString(),
            'jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status' => 'pending',
            'created_by' => $tu->id,
        ]);

        PenyerahanFaktur::create([
            'tu_faktur_id' => $fakturC->id,
            'siswa_id' => $siswa->id,
            'berkas_file' => 'penyerahan_faktur/c.jpg',
            'status' => 'diverifikasi',
            'verified_by' => $tu->id,
            'verified_at' => now('Asia/Jakarta'),
        ]);
        PenyerahanFaktur::create([
            'tu_faktur_id' => $fakturD->id,
            'siswa_id' => $siswa->id,
            'berkas_file' => 'penyerahan_faktur/d.jpg',
            'status' => 'ditolak',
            'catatan_penolakan' => 'Bukti belum valid',
            'verified_by' => $tu->id,
            'verified_at' => now('Asia/Jakarta'),
        ]);

        $response = $this->actingAs($ortu)->get(route('ortu.dashboard'));

        $response->assertOk()
            ->assertViewIs('ortu.dashboard')
            ->assertViewHas('stats', function (array $stats) {
                return ($stats['total_faktur'] ?? null) === 4
                    && ($stats['total_berjalan'] ?? null) === 2
                    && ($stats['total_diterima'] ?? null) === 1
                    && ($stats['total_ditolak'] ?? null) === 1;
            });
    }
}
