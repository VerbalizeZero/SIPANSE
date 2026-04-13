<?php

namespace Tests\Feature\Arsip;

use App\Models\MasterFaktur;
use App\Models\TuFaktur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BendaharaArsipWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $bendaharaUser;
    private User $tuUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bendaharaUser = User::factory()->create(['role' => 'bendahara']);
        $this->tuUser = User::factory()->create(['role' => 'tu']);
    }

    public function test_only_bendahara_can_access_bendahara_arsip_menu(): void
    {
        $this->get(route('bendahara.arsip.index'))->assertRedirect(route('login'));

        $this->actingAs($this->tuUser)
            ->get(route('bendahara.arsip.index'))
            ->assertForbidden();

        $this->actingAs($this->bendaharaUser)
            ->get(route('bendahara.arsip.index'))
            ->assertOk()
            ->assertViewIs('bendahara.arsip.index');
    }

    public function test_bendahara_arsip_index_only_shows_diarsipkan_sublist(): void
    {
        $master = MasterFaktur::create([
            'nama_faktur' => 'SPP Arsip',
            'nominal' => 250000,
        ]);

        TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'kelas',
            'target_value' => 'X-A',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status' => 'pending',
            'created_by' => $this->tuUser->id,
        ]);

        TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'kelas',
            'target_value' => 'X-B',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status' => 'diarsipkan',
            'created_by' => $this->tuUser->id,
        ]);

        $response = $this->actingAs($this->bendaharaUser)
            ->get(route('bendahara.arsip.index'));

        $response->assertOk();
        $response->assertSee('X-B');
        $response->assertDontSee('X-A');
    }

    public function test_bendahara_arsip_supports_filter_search_and_pagination(): void
    {
        $masterA = MasterFaktur::create([
            'nama_faktur' => 'SPP April',
            'nominal' => 300000,
        ]);
        $masterB = MasterFaktur::create([
            'nama_faktur' => 'Ujian Praktik',
            'nominal' => 175000,
        ]);

        $match = TuFaktur::create([
            'master_faktur_id' => $masterA->id,
            'target_type' => 'kelas',
            'target_value' => 'X-C',
            'tersedia_pada' => '2026-04-01',
            'jatuh_tempo' => '2026-04-20',
            'status' => 'diarsipkan',
            'created_by' => $this->tuUser->id,
        ]);
        $match->created_at = '2026-04-10 09:00:00';
        $match->updated_at = '2026-04-10 09:00:00';
        $match->save();

        $other = TuFaktur::create([
            'master_faktur_id' => $masterB->id,
            'target_type' => 'kelas',
            'target_value' => 'XI-A',
            'tersedia_pada' => '2026-03-01',
            'jatuh_tempo' => '2026-03-15',
            'status' => 'diarsipkan',
            'created_by' => $this->tuUser->id,
        ]);
        $other->created_at = '2026-03-10 09:00:00';
        $other->updated_at = '2026-03-10 09:00:00';
        $other->save();

        $response = $this->actingAs($this->bendaharaUser)->get(route('bendahara.arsip.index', [
            'bulan' => '2026-04',
            'kelas' => 'X-C',
            'search' => 'SPP',
        ]));

        $response->assertOk();
        $response->assertSee('SPP April');
        $response->assertDontSee('Ujian Praktik');
        $response->assertViewHas('fakturs', fn ($fakturs) => method_exists($fakturs, 'links'));
    }

    public function test_bendahara_can_export_csv_per_sublist_for_diarsipkan_data(): void
    {
        $master = MasterFaktur::create([
            'nama_faktur' => 'Laporan Kas Final',
            'nominal' => 500000,
        ]);

        $faktur = TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'semua_siswa',
            'target_value' => 'Semua Siswa',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'diarsipkan',
            'created_by' => $this->tuUser->id,
        ]);

        $response = $this->actingAs($this->bendaharaUser)
            ->post(route('bendahara.arsip.export_sublist', $faktur));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString(
            'attachment; filename=Arsip_Bendahara_Sublist_Laporan Kas Final_',
            $response->headers->get('Content-Disposition')
        );
    }

    public function test_bendahara_can_export_csv_global_from_filtered_archive(): void
    {
        $master = MasterFaktur::create([
            'nama_faktur' => 'SPP Final April',
            'nominal' => 275000,
        ]);

        $faktur = TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'kelas',
            'target_value' => 'X-D',
            'tersedia_pada' => '2026-04-01',
            'jatuh_tempo' => '2026-04-20',
            'status' => 'diarsipkan',
            'created_by' => $this->tuUser->id,
        ]);
        $faktur->created_at = '2026-04-12 09:00:00';
        $faktur->updated_at = '2026-04-12 09:00:00';
        $faktur->save();

        $response = $this->actingAs($this->bendaharaUser)
            ->get(route('bendahara.arsip.export_global', [
                'bulan' => '2026-04',
                'kelas' => 'X-D',
                'search' => 'SPP',
            ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString(
            'attachment; filename=Arsip_Bendahara_Global_2026-04_',
            $response->headers->get('Content-Disposition')
        );
    }
}
