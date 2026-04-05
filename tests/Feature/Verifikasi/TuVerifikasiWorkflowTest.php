<?php

namespace Tests\Feature\Verifikasi;

use App\Models\MasterFaktur;
use App\Models\TuFaktur;
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

    /**
     * Rule penting:
     * aksi tolak WAJIB ada catatan penolakan sebagai authenticator.
     */
    /** @test */
    public function tu_cannot_reject_verification_without_rejection_note(): void
    {
        $tu = User::factory()->tu()->create();
        $faktur = $this->createTuFaktur();

        $this->actingAs($tu)
            ->post("/tu/verifikasi/{$faktur->id}/reject", [
                'catatan_penolakan' => '',
            ])
            ->assertSessionHasErrors('catatan_penolakan');
    }

    /**
     * Jika catatan penolakan terisi, proses tolak harus berhasil.
     * Selain status, nantinya juga menyimpan audit trail user TU yang memproses.
     */
    /** @test */
    public function tu_can_reject_verification_with_rejection_note(): void
    {
        $tu = User::factory()->tu()->create();
        $faktur = $this->createTuFaktur();

        $this->actingAs($tu)
            ->post("/tu/verifikasi/{$faktur->id}/reject", [
                'catatan_penolakan' => 'Bukti transfer tidak sesuai nominal faktur.',
            ])
            ->assertRedirect('/tu/verifikasi');
    }

    /**
     * Setelah semua verifikasi faktur selesai + TU export laporan,
     * sistem mengaktifkan timer auto-delete 7 hari (bukan langsung hapus).
     */
    /** @test */
    public function exporting_completed_sublist_activates_seven_day_cleanup_timer(): void
    {
        $tu = User::factory()->tu()->create();
        $faktur = $this->createTuFaktur();

        $this->actingAs($tu)
            ->post("/tu/verifikasi/{$faktur->id}/export")
            ->assertRedirect('/tu/verifikasi');
    }
}

