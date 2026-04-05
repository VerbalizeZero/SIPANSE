<?php

namespace Tests\Feature\Faktur;

use App\Models\MasterFaktur;
use App\Models\TuFaktur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TuFakturManagementTest extends TestCase
{
    use RefreshDatabase;

    // Role TU dapat membuka halaman indeks faktur.
    /** @test */
    public function tu_can_access_faktur_index_page(): void
    {
        $tu = User::factory()->tu()->create();

        $this->actingAs($tu)
            ->get('/tu/faktur')
            ->assertOk();
    }

    // Role selain TU harus ditolak.
    /** @test */
    public function non_tu_cannot_access_faktur_index_page(): void
    {
        $bendahara = User::factory()->bendahara()->create();

        $this->actingAs($bendahara)
            ->get('/tu/faktur')
            ->assertForbidden();
    }

    // Guest wajib login dulu.
    /** @test */
    public function guest_cannot_access_faktur_index_page(): void
    {
        $this->get('/tu/faktur')
            ->assertRedirect(route('login'));
    }

    // Filter bulan + search nama faktur harus bekerja bersamaan.
    /** @test */
    public function tu_can_request_faktur_index_with_filter_and_search_query(): void
    {
        $tu = User::factory()->tu()->create();
        $masterA = MasterFaktur::create([
            'jenis_faktur' => 'SPP',
            'nama_faktur' => 'SPP Maret',
            'nominal' => 250000,
            'deskripsi' => null,
        ]);
        $masterB = MasterFaktur::create([
            'jenis_faktur' => 'Ujian',
            'nama_faktur' => 'Ujian Praktik',
            'nominal' => 150000,
            'deskripsi' => null,
        ]);

        $fakturMaret = TuFaktur::create([
            'master_faktur_id' => $masterA->id,
            'target_type' => 'angkatan',
            'target_value' => '2027',
            'tersedia_pada' => '2026-03-01',
            'jatuh_tempo' => '2026-03-30',
            'status' => 'Pending',
        ]);
        $fakturMaret->created_at = '2026-03-10 10:00:00';
        $fakturMaret->updated_at = '2026-03-10 10:00:00';
        $fakturMaret->save();

        $fakturApril = TuFaktur::create([
            'master_faktur_id' => $masterB->id,
            'target_type' => 'kelas',
            'target_value' => 'X-A',
            'tersedia_pada' => '2026-04-01',
            'jatuh_tempo' => '2026-04-30',
            'status' => 'Pending',
        ]);
        $fakturApril->created_at = '2026-04-10 10:00:00';
        $fakturApril->updated_at = '2026-04-10 10:00:00';
        $fakturApril->save();

        $response = $this->actingAs($tu)
            ->get('/tu/faktur?bulan=2026-03&search=SPP')
            ->assertOk();

        $response->assertViewHas('fakturs', function ($fakturs) {
            $items = method_exists($fakturs, 'items') ? collect($fakturs->items()) : collect($fakturs);
            $names = $items->map(fn ($faktur) => $faktur->masterFaktur?->nama_faktur)->filter()->values();

            return $names->contains('SPP Maret')
                && !$names->contains('Ujian Praktik')
                && $items->count() === 1;
        });

        $faktur = TuFaktur::create([
            'master_faktur_id' => $masterA->id,
            'target_type' => 'kelas',
            'target_value' => 'X-B',
            'tersedia_pada' => '2026-03-15',
            'jatuh_tempo' => '2026-03-31',
            'status' => 'Pending',
        ]);
            $faktur->created_at = '2026-03-15 10:00:00';
            $faktur->save();      
    }

    // Create faktur TU.
    /** @test */
    public function tu_can_store_faktur(): void
    {
        $tu = User::factory()->tu()->create();
        $masterFaktur = MasterFaktur::create([
            'jenis_faktur' => 'SPP',
            'nama_faktur' => 'SPP Bulanan',
            'nominal' => 250000,
            'deskripsi' => null,
        ]);

        $response = $this->actingAs($tu)
            ->post(route('tu.faktur.store'), [
                'master_faktur_id' => $masterFaktur->id,
                'target_type' => 'kelas',
                'target_value' => 'X-A',
                'tersedia_pada' => '2026-03-01',
                'jatuh_tempo' => '2026-03-30',
                'status' => 'Pending',
            ]);

        $response->assertRedirect(route('tu.faktur.index'));
        $this->assertDatabaseHas('tu_fakturs', [
            'master_faktur_id' => $masterFaktur->id,
            'target_type' => 'kelas',
            'target_value' => 'X-A',
            'status' => 'Pending',
        ]);
    }

    // Update dan delete faktur TU.
    /** @test */
    public function tu_can_update_and_delete_faktur(): void
    {
        $tu = User::factory()->tu()->create();
        $masterFaktur = MasterFaktur::create([
            'jenis_faktur' => 'SPP',
            'nama_faktur' => 'SPP Bulanan',
            'nominal' => 250000,
            'deskripsi' => null,
        ]);
        $tuFaktur = TuFaktur::create([
            'master_faktur_id' => $masterFaktur->id,
            'target_type' => 'angkatan',
            'target_value' => '2027',
            'tersedia_pada' => '2026-03-01',
            'jatuh_tempo' => '2026-03-30',
            'status' => 'Pending',
        ]);

        $updateResponse = $this->actingAs($tu)
            ->put(route('tu.faktur.update', $tuFaktur), [
                'master_faktur_id' => $masterFaktur->id,
                'target_type' => 'semua_siswa',
                'target_value' => null,
                'tersedia_pada' => '2026-03-01',
                'jatuh_tempo' => '2026-04-15',
                'status' => 'Selesai',
            ]);

        $updateResponse->assertRedirect(route('tu.faktur.index'));
        $this->assertDatabaseHas('tu_fakturs', [
            'id' => $tuFaktur->id,
            'target_type' => 'semua_siswa',
            'status' => 'Selesai',
        ]);

        $deleteResponse = $this->actingAs($tu)
            ->delete(route('tu.faktur.destroy', $tuFaktur));

        $deleteResponse->assertRedirect(route('tu.faktur.index'));
        $this->assertDatabaseMissing('tu_fakturs', [
            'id' => $tuFaktur->id,
        ]);
    }

    
}
