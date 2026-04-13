<?php

namespace Tests\Feature\Verifikasi;

use App\Models\MasterFaktur;
use App\Models\Siswa;
use App\Models\TuFaktur;
use App\Models\PenyerahanFaktur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TuVerifikasiWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper untuk membuat 1 faktur TU sebagai basis pengujian endpoint verifikasi.
     */
    private function createTuFaktur(): TuFaktur
    {
        $master = MasterFaktur::create([
            'jenis_faktur' => 'SPP',
            'nama_faktur' => 'SPP April',
            'nominal' => 250000,
            'deskripsi' => null,
        ]);

        return TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'angkatan',
            'target_value' => '2027',
            'tersedia_pada' => '2026-04-01',
            'jatuh_tempo' => '2026-04-30',
            'status' => 'Pending',
        ]);
    }

    /**
     * Helper membuat siswa yang masuk target faktur (angkatan 2027).
     */
    private function createTargetSiswa(): Siswa
    {
        return Siswa::create([
            'nisn' => '1202988374',
            'nama_siswa' => 'Dober Mejiro',
            'tahun_angkatan' => '2027',
            'kelas' => 'D',
            'jenis_kelamin' => 'Laki-laki',
        ]);
    }

    /** @test */
    public function tu_can_access_verifikasi_index_page(): void
    {
        $tu = User::factory()->tu()->create();

        $this->actingAs($tu)
            ->get('/tu/verifikasi')
            ->assertOk();
    }

    /** @test */
    public function non_tu_cannot_access_verifikasi_index_page(): void
    {
        $bendahara = User::factory()->bendahara()->create();

        $this->actingAs($bendahara)
            ->get('/tu/verifikasi')
            ->assertForbidden();
    }

    /** @test */
    public function guest_cannot_access_verifikasi_index_page(): void
    {
        $this->get('/tu/verifikasi')
            ->assertRedirect(route('login'));
    }

    /**
     * Halaman list verifikasi diharapkan menampilkan grup per tanggal/bulan pembuatan faktur,
     * termasuk metadata audit trail pembuat faktur TU.
     */
    /** @test */
    public function verifikasi_index_can_show_grouped_faktur_list_with_creator_metadata(): void
    {
        $tu = User::factory()->tu()->create();
        $this->createTuFaktur();

        $this->actingAs($tu)
            ->get('/tu/verifikasi')
            ->assertOk()
            ->assertSee('SPP April');
    }

    /**
     * TU membuka sublist/detail verifikasi untuk satu faktur.
     * Detail ini nantinya berisi daftar siswa target + status verifikasi per siswa.
     */
    /** @test */
    public function tu_can_open_verifikasi_detail_page_for_specific_faktur(): void
    {
        $tu = User::factory()->tu()->create();
        $faktur = $this->createTuFaktur();

        $this->actingAs($tu)
            ->get("/tu/verifikasi/{$faktur->id}")
            ->assertOk();
    }

    /** @test */
    public function tu_can_update_status_siswa_to_diverifikasi(): void
    {
        $tu = User::factory()->tu()->create();
        $faktur = $this->createTuFaktur();
        $siswa = $this->createTargetSiswa();
        PenyerahanFaktur::create([
            'tu_faktur_id' => $faktur->id,
            'siswa_id' => $siswa->id,
            'berkas_file' => 'penyerahan_faktur/bukti-awal.jpg',
            'status' => 'menunggu_verifikasi',
            'catatan_penolakan' => null,
        ]);

        $this->actingAs($tu)
            ->post("/tu/verifikasi/{$faktur->id}/siswa/{$siswa->id}/status", [
                'status' => 'diverifikasi',
                'catatan_penolakan' => null,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'sublist_status' => 'selesai',
            ]);

        $this->assertDatabaseHas('penyerahan_fakturs', [
            'tu_faktur_id' => $faktur->id,
            'siswa_id' => $siswa->id,
            'status' => 'diverifikasi',
        ]);
    }

    /** @test */
    public function tu_can_update_status_siswa_to_ditolak_with_note(): void
    {
        $tu = User::factory()->tu()->create();
        $faktur = $this->createTuFaktur();
        $siswa = $this->createTargetSiswa();
        PenyerahanFaktur::create([
            'tu_faktur_id' => $faktur->id,
            'siswa_id' => $siswa->id,
            'berkas_file' => 'penyerahan_faktur/bukti.jpg',
            'status' => 'menunggu_verifikasi',
            'catatan_penolakan' => null,
        ]);

        $this->actingAs($tu)
            ->post("/tu/verifikasi/{$faktur->id}/siswa/{$siswa->id}/status", [
                'status' => 'ditolak',
                'catatan_penolakan' => 'Bukti transfer tidak sesuai nominal faktur.',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'sublist_status' => 'Pending',
            ]);

        $this->assertDatabaseHas('penyerahan_fakturs', [
            'tu_faktur_id' => $faktur->id,
            'siswa_id' => $siswa->id,
            'status' => 'ditolak',
            'catatan_penolakan' => 'Bukti transfer tidak sesuai nominal faktur.',
        ]);
    }

    /** @test */
    public function exporting_sublist_returns_csv_stream_response(): void
    {
        $tu = User::factory()->tu()->create();
        $faktur = $this->createTuFaktur();

        $this->actingAs($tu)
            ->post("/tu/verifikasi/{$faktur->id}/export")
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
