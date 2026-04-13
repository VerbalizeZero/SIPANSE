<?php

namespace Tests\Feature\Arsip;

use App\Models\MasterFaktur;
use App\Models\Siswa;
use App\Models\TuFaktur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TuArsipWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $tuUser;
    private User $ortuUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup User TU
        $this->tuUser = User::factory()->create(['role' => 'tu']);
        
        // Setup User Ortu
        $this->ortuUser = User::factory()->create(['role' => 'orang_tua']);
    }

    public function test_only_tu_can_access_arsip_menu()
    {
        // Akses ditolak jika belum login
        $this->get(route('tu.arsip.index'))->assertRedirect(route('login'));

        // Akses ditolak untuk role Ortu
        $this->actingAs($this->ortuUser)
            ->get(route('tu.arsip.index'))
            ->assertForbidden();

        // Akses diizinkan untuk TU
        $this->actingAs($this->tuUser)
            ->get(route('tu.arsip.index'))
            ->assertOk()
            ->assertViewIs('tu.arsip.index');
    }

    public function test_arsip_index_only_shows_diarsipkan_fakturs()
    {
        $masterFaktur = MasterFaktur::create([
            'nama_faktur' => 'SPP April 2026',
            'nominal' => 250000,
        ]);

        // Faktur aktif (belum arsip)
        $fakturAktif = TuFaktur::create([
            'master_faktur_id' => $masterFaktur->id,
            'target_type' => 'semua_siswa',
            'target_value' => 'Semua Siswa',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'pending',
            'created_by' => $this->tuUser->id,
        ]);

        // Faktur arsip
        $fakturArsip = TuFaktur::create([
            'master_faktur_id' => $masterFaktur->id,
            'target_type' => 'kelas',
            'target_value' => 'X-A',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'diarsipkan',
            'created_by' => $this->tuUser->id,
        ]);

        $response = $this->actingAs($this->tuUser)->get(route('tu.arsip.index'));

        $response->assertOk();
        // Harus melihat faktur arsip (target kelas X-A)
        $response->assertSee('X-A');
        // Tidak boleh melihat faktur aktif (target Semua Siswa) yang datanya belum arsip
        $response->assertDontSee('Semua Siswa');
    }

    public function test_export_sublist_fungsi_sukses()
    {
        $masterFaktur = MasterFaktur::create([
            'nama_faktur' => 'Uang Gedung 2026',
            'nominal' => 1500000,
        ]);

        $fakturArsip = TuFaktur::create([
            'master_faktur_id' => $masterFaktur->id,
            'target_type' => 'angkatan',
            'target_value' => '2026',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'diarsipkan',
            'created_by' => $this->tuUser->id,
        ]);

        // Test stream CSV response
        $response = $this->actingAs($this->tuUser)->post(route('tu.arsip.export_sublist', $fakturArsip));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=Arsip_Sublist_Uang Gedung 2026_' . now()->format('Ymd') . '.csv');
    }

    public function test_export_global_fungsi_sukses()
    {
        $masterFaktur1 = MasterFaktur::create([
            'nama_faktur' => 'SPP',
            'nominal' => 100000,
        ]);
        $masterFaktur2 = MasterFaktur::create([
            'nama_faktur' => 'Eskul',
            'nominal' => 50000,
        ]);

        TuFaktur::create([
            'master_faktur_id' => $masterFaktur1->id,
            'target_type' => 'semua_siswa',
            'target_value' => 'Semua Siswa',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'diarsipkan',
            'created_by' => $this->tuUser->id,
        ]);

        TuFaktur::create([
            'master_faktur_id' => $masterFaktur2->id,
            'target_type' => 'semua_siswa',
            'target_value' => 'Semua Siswa',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'diarsipkan',
            'created_by' => $this->tuUser->id,
        ]);

        // Request global export
        $response = $this->actingAs($this->tuUser)->get(route('tu.arsip.export_global'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        // Pastikan nama filenya Arsip_Global_SemuaBulan
        $this->assertStringContainsString('Arsip_Global_SemuaBulan_', $response->headers->get('Content-Disposition'));
    }
}
