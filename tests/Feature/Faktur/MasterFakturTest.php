<?php

namespace Tests\Feature\Faktur;

use App\Models\MasterFaktur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterFakturTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bendahara_can_access_master_faktur_index_page(): void
    {
        $bendahara = User::factory()->bendahara()->create();

        $this->actingAs($bendahara)
            ->get(route('bendahara.master-faktur.index'))
            ->assertOk();
    }

    /** @test */
    public function non_bendahara_cannot_access_master_faktur_index_page(): void
    {
        $tu = User::factory()->tu()->create();

        $this->actingAs($tu)
            ->get(route('bendahara.master-faktur.index'))
            ->assertForbidden();
    }

    /** @test */
    public function guest_cannot_access_master_faktur_index_page(): void
    {
        $this->get(route('bendahara.master-faktur.index'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function bendahara_can_store_master_faktur(): void
    {
        $bendahara = User::factory()->bendahara()->create();

        $response = $this->actingAs($bendahara)->post(route('bendahara.master-faktur.store'), [
            'jenis_faktur' => 'SPP',
            'nama_faktur' => 'SPP Bulanan',
            'nominal' => 250000,
            'deskripsi' => 'Faktur SPP untuk siswa',
        ]);

        $response->assertRedirect(route('bendahara.master-faktur.index'));
        $this->assertDatabaseHas('master_fakturs', [
            'jenis_faktur' => 'SPP',
            'nama_faktur' => 'SPP Bulanan',
            'nominal' => 250000,
        ]);
    }

    /** @test */
    public function bendahara_cannot_store_master_faktur_with_invalid_data(): void
    {
        $bendahara = User::factory()->bendahara()->create();

        $response = $this->from(route('bendahara.master-faktur.index'))
            ->actingAs($bendahara)
            ->post(route('bendahara.master-faktur.store'), [
                'jenis_faktur' => '',
                'nama_faktur' => '',
                'nominal' => -1000,
            ]);

        $response->assertRedirect(route('bendahara.master-faktur.index'));
        $response->assertSessionHasErrors(['jenis_faktur', 'nama_faktur', 'nominal']);
    }

    /** @test */
    public function bendahara_can_update_master_faktur(): void
    {
        $bendahara = User::factory()->bendahara()->create();
        $masterFaktur = MasterFaktur::create([
            'jenis_faktur' => 'SPP',
            'nama_faktur' => 'SPP Lama',
            'nominal' => 100000,
            'deskripsi' => 'Deskripsi lama',
        ]);

        $response = $this->actingAs($bendahara)
            ->put(route('bendahara.master-faktur.update', $masterFaktur), [
                'jenis_faktur' => 'Kegiatan',
                'nama_faktur' => 'SPP Diperbarui',
                'nominal' => 150000,
                'deskripsi' => 'Deskripsi baru',
            ]);

        $response->assertRedirect(route('bendahara.master-faktur.index'));
        $this->assertDatabaseHas('master_fakturs', [
            'id' => $masterFaktur->id,
            'jenis_faktur' => 'Kegiatan',
            'nama_faktur' => 'SPP Diperbarui',
            'nominal' => 150000,
            'deskripsi' => 'Deskripsi baru',
        ]);
    }

    /** @test */
    public function bendahara_can_delete_master_faktur(): void
    {
        $bendahara = User::factory()->bendahara()->create();
        $masterFaktur = MasterFaktur::create([
            'jenis_faktur' => 'SPP',
            'nama_faktur' => 'Faktur Akan Dihapus',
            'nominal' => 90000,
            'deskripsi' => null,
        ]);

        $response = $this->actingAs($bendahara)
            ->delete(route('bendahara.master-faktur.destroy', $masterFaktur));

        $response->assertRedirect(route('bendahara.master-faktur.index'));
        $this->assertDatabaseMissing('master_fakturs', [
            'id' => $masterFaktur->id,
        ]);
    }

    /** @test */
    public function non_bendahara_cannot_modify_master_faktur(): void
    {
        $tu = User::factory()->tu()->create();
        $masterFaktur = MasterFaktur::create([
            'jenis_faktur' => 'SPP',
            'nama_faktur' => 'SPP Uji',
            'nominal' => 200000,
            'deskripsi' => null,
        ]);

        $this->actingAs($tu)
            ->put(route('bendahara.master-faktur.update', $masterFaktur), [
                'jenis_faktur' => 'Buku',
                'nama_faktur' => 'Tidak Boleh',
                'nominal' => 300000,
                'deskripsi' => 'Tidak boleh diubah',
            ])
            ->assertForbidden();

        $this->actingAs($tu)
            ->delete(route('bendahara.master-faktur.destroy', $masterFaktur))
            ->assertForbidden();
    }

    /** @test */
    public function bendahara_can_filter_master_faktur_by_jenis_and_search_by_nama(): void
    {
        $bendahara = User::factory()->bendahara()->create();

        MasterFaktur::create([
            'jenis_faktur' => 'SPP',
            'nama_faktur' => 'SPP Bulanan Kelas X',
            'nominal' => 500000,
            'deskripsi' => null,
        ]);

        MasterFaktur::create([
            'jenis_faktur' => 'Ujian',
            'nama_faktur' => 'Ujian Praktik',
            'nominal' => 200000,
            'deskripsi' => null,
        ]);

        $response = $this->actingAs($bendahara)->get(route('bendahara.master-faktur.index', [
            'jenis_faktur' => 'SPP',
            'search' => 'Kelas X',
        ]));

        $response->assertOk();
        $response->assertSee('SPP Bulanan Kelas X');
        $response->assertDontSee('Ujian Praktik');
    }
}
