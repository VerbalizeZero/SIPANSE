<?php

namespace Tests\Feature\Ortu;

use App\Models\MasterFaktur;
use App\Models\Notifikasi;
use App\Models\Siswa;
use App\Models\TuFaktur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifikasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_ortu_gets_notification_when_tu_creates_available_faktur(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $siswa = Siswa::create([
            'nisn' => '1234567890',
            'nama_siswa' => 'Budi Santoso',
            'kelas' => 'X-A',
            'tahun_angkatan' => '2027',
        ]);
        $ortu = User::where('nisn', '1234567890')->where('role', 'orang_tua')->first();
        $this->assertNotNull($ortu);

        $master = MasterFaktur::create([
            'nama_faktur' => 'SPP Bulan Januari',
            'nominal' => 500000,
            'jenis' => 'bulanan',
        ]);

        $this->actingAs($tu);
        $response = $this->post(route('tu.faktur.store'), [
            'master_faktur_id' => $master->id,
            'target_type' => 'kelas',
            'target_value' => 'X-A',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertRedirect(route('tu.faktur.index'));

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $ortu->id,
            'title' => 'Faktur Baru Tersedia',
        ]);
    }

    public function test_ortu_does_not_get_notification_when_faktur_not_yet_available(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $siswa = Siswa::create([
            'nisn' => '1234567891',
            'nama_siswa' => 'Ani Wijaya',
            'kelas' => 'X-B',
            'tahun_angkatan' => '2027',
        ]);
        $ortu = User::where('nisn', '1234567891')->where('role', 'orang_tua')->first();
        $this->assertNotNull($ortu);

        $master = MasterFaktur::create([
            'nama_faktur' => 'SPP Bulan Februari',
            'nominal' => 500000,
            'jenis' => 'bulanan',
        ]);

        $this->actingAs($tu);
        $response = $this->post(route('tu.faktur.store'), [
            'master_faktur_id' => $master->id,
            'target_type' => 'kelas',
            'target_value' => 'X-B',
            'tersedia_pada' => now()->addDays(3)->toDateString(),
            'jatuh_tempo' => now()->addDays(10)->toDateString(),
        ]);

        $response->assertRedirect(route('tu.faktur.index'));

        $this->assertDatabaseMissing('notifikasis', [
            'user_id' => $ortu->id,
        ]);
    }

    public function test_ortu_can_see_notification_badge_and_mark_as_read(): void
    {
        // Buat siswa agar observer membuat user ortu, lalu login sebagai ortu
        $siswa = Siswa::create([
            'nisn' => '1234567893',
            'nama_siswa' => 'Dedi Kurniawan',
            'kelas' => 'X-D',
            'tahun_angkatan' => '2027',
        ]);
        $ortu = User::where('nisn', '1234567893')->where('role', 'orang_tua')->first();
        $this->assertNotNull($ortu);

        $notif = Notifikasi::create([
            'user_id' => $ortu->id,
            'title' => 'Faktur Baru Tersedia',
            'message' => 'Ada faktur baru',
            'url' => route('ortu.faktur.index'),
        ]);

        $this->actingAs($ortu);
        $response = $this->get(route('ortu.faktur.index'));
        $response->assertOk();
        $response->assertSee('Notifikasi');
        $response->assertSee('Faktur Baru Tersedia');

        // Mark as read
        $readResponse = $this->get(route('ortu.notifikasi.read', $notif));
        $readResponse->assertRedirect(route('ortu.faktur.index'));

        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_ortu_can_mark_all_notifications_as_read(): void
    {
        $ortu = User::factory()->create(['role' => 'orang_tua']);
        Notifikasi::create([
            'user_id' => $ortu->id,
            'title' => 'Faktur 1',
            'message' => 'Msg 1',
            'url' => route('ortu.faktur.index'),
        ]);
        Notifikasi::create([
            'user_id' => $ortu->id,
            'title' => 'Faktur 2',
            'message' => 'Msg 2',
            'url' => route('ortu.faktur.index'),
        ]);

        $this->actingAs($ortu);
        $response = $this->post(route('ortu.notifikasi.read-all'));
        $response->assertRedirect();

        $this->assertEquals(0, Notifikasi::where('user_id', $ortu->id)->whereNull('read_at')->count());
    }

    public function test_command_sends_notification_for_faktur_that_becomes_available_today(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $siswa = Siswa::create([
            'nisn' => '1234567892',
            'nama_siswa' => 'Citra Lestari',
            'kelas' => 'X-C',
            'tahun_angkatan' => '2027',
        ]);
        $ortu = User::where('nisn', '1234567892')->where('role', 'orang_tua')->first();
        $this->assertNotNull($ortu);

        $master = MasterFaktur::create([
            'nama_faktur' => 'SPP Bulan Maret',
            'nominal' => 500000,
            'jenis' => 'bulanan',
        ]);

        $faktur = TuFaktur::create([
            'master_faktur_id' => $master->id,
            'created_by' => $tu->id,
            'target_type' => 'kelas',
            'target_value' => 'X-C',
            'tersedia_pada' => now()->subDay()->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'pending',
        ]);

        $this->artisan('notifikasi:kirim-faktur-tersedia')
            ->assertSuccessful();

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $ortu->id,
            'title' => 'Faktur Baru Tersedia',
        ]);

        $this->assertNotNull($faktur->fresh()->notifikasi_dikirim_at);
    }

    public function test_ortu_gets_notification_when_faktur_is_verified_as_diverifikasi(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $siswa = Siswa::create([
            'nisn' => '1234567894',
            'nama_siswa' => 'Eko Prasetyo',
            'kelas' => 'X-D',
            'tahun_angkatan' => '2027',
        ]);
        $ortu = User::where('nisn', '1234567894')->where('role', 'orang_tua')->first();
        $this->assertNotNull($ortu);

        $master = MasterFaktur::create([
            'nama_faktur' => 'SPP Bulan April',
            'nominal' => 500000,
            'jenis' => 'bulanan',
        ]);

        $faktur = TuFaktur::create([
            'master_faktur_id' => $master->id,
            'created_by' => $tu->id,
            'target_type' => 'siswa',
            'target_value' => '1234567894',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'pending',
        ]);

        // Buat penyerahan terlebih dahulu agar updateStatusSiswa tidak perlu firstOrNew
        \App\Models\PenyerahanFaktur::create([
            'tu_faktur_id' => $faktur->id,
            'siswa_id' => $siswa->id,
            'berkas_file' => 'dummy.pdf',
            'status' => 'menunggu_verifikasi',
        ]);

        $this->actingAs($tu);
        $response = $this->postJson(route('tu.verifikasi.update_status_siswa', [$faktur, $siswa]), [
            'status' => 'diverifikasi',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $ortu->id,
            'title' => 'Faktur Diterima',
        ]);
    }

    public function test_ortu_gets_notification_when_faktur_is_verified_as_ditolak(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);
        $siswa = Siswa::create([
            'nisn' => '1234567895',
            'nama_siswa' => 'Fani Susanti',
            'kelas' => 'X-E',
            'tahun_angkatan' => '2027',
        ]);
        $ortu = User::where('nisn', '1234567895')->where('role', 'orang_tua')->first();
        $this->assertNotNull($ortu);

        $master = MasterFaktur::create([
            'nama_faktur' => 'SPP Bulan Mei',
            'nominal' => 500000,
            'jenis' => 'bulanan',
        ]);

        $faktur = TuFaktur::create([
            'master_faktur_id' => $master->id,
            'created_by' => $tu->id,
            'target_type' => 'siswa',
            'target_value' => '1234567895',
            'tersedia_pada' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'pending',
        ]);

        // Buat penyerahan terlebih dahulu agar updateStatusSiswa tidak perlu firstOrNew
        \App\Models\PenyerahanFaktur::create([
            'tu_faktur_id' => $faktur->id,
            'siswa_id' => $siswa->id,
            'berkas_file' => 'dummy.pdf',
            'status' => 'menunggu_verifikasi',
        ]);

        $this->actingAs($tu);
        $response = $this->postJson(route('tu.verifikasi.update_status_siswa', [$faktur, $siswa]), [
            'status' => 'ditolak',
            'catatan_penolakan' => 'Berkas kurang jelas',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $ortu->id,
            'title' => 'Faktur Ditolak',
        ]);
    }

    public function test_old_notifications_are_pruned_after_3_days(): void
    {
        $ortu = User::factory()->create(['role' => 'orang_tua']);
        $notif = Notifikasi::create([
            'user_id' => $ortu->id,
            'title' => 'Lama',
            'message' => 'Pesan lama',
            'url' => route('ortu.faktur.index'),
        ]);

        // Manually set created_at to 4 days ago via query builder to bypass model mutators
        \DB::table('notifikasis')->where('id', $notif->id)->update([
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        \App\Services\NotifikasiService::pruneOldNotifications(3);

        $this->assertDatabaseMissing('notifikasis', [
            'id' => $notif->id,
        ]);
    }
}
