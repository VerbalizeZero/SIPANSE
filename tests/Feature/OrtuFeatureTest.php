<?php

namespace Tests\Feature;

use App\Models\Siswa;
use App\Models\User;
use App\Models\MasterFaktur;
use App\Models\TuFaktur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrtuFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_auto_creates_user_ortu_when_siswa_is_created(): void
    {
        // Act
        $siswa = Siswa::create([
            'nisn' => '1234567890',
            'nama_siswa' => 'Budi Sudarsono',
            'tahun_angkatan' => '2026',
            'jenis_kelamin' => 'L',
        ]);

        // Assert
        $this->assertDatabaseHas('users', [
            'nisn' => '1234567890',
            'name' => 'Budi Sudarsono',
            'role' => 'orang_tua'
        ]);
    }

    public function test_ortu_can_login_using_only_nisn(): void
    {
        $siswa = Siswa::create([
            'nisn' => '9876543210',
            'nama_siswa' => 'Agus Salim',
        ]);

        // Post ke rute login khusus ortu
        $response = $this->post('/ortu/login', [
            'nisn' => '9876543210'
        ]);

        $response->assertRedirect('/ortu/faktur');
        $this->assertAuthenticated();

        // Validasi gagal jika nisn ngawur
        $responseFail = $this->post('/ortu/login', [
            'nisn' => '11111'
        ]);
        
        $responseFail->assertSessionHasErrors(['nisn']);
    }

    public function test_ortu_cannot_submit_without_uploading_file(): void
    {
        $siswa = Siswa::create([
            'nisn' => '0000000000',
            'nama_siswa' => 'Citra',
        ]);

        $user = User::where('nisn', '0000000000')->first();
        
        // Buat dummy faktur
        $userAdmin = User::factory()->create(['role' => 'tu']);
        $master = MasterFaktur::create(['nama_faktur' => 'SPP', 'nominal' => 200000, 'jenis_faktur' => 'rutin']);
        $faktur = TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'siswa',
            'target_value' => 'Citra - 0000000000', // Sesuai sistem saat ini
            'status' => 'Berjalan',
            'tersedia_pada' => now()->format('Y-m-d'),
            'jatuh_tempo' => now()->addDays(7)->format('Y-m-d'),
            'created_by' => $userAdmin->id,
        ]);

        // Act & Assert tanpa file
        $response = $this->actingAs($user)->post("/ortu/faktur/{$faktur->id}/submit", []);
        
        $response->assertSessionHasErrors(['berkas_file']);
        $this->assertDatabaseEmpty('penyerahan_fakturs');
    }

    public function test_ortu_can_submit_with_file(): void
    {
        Storage::fake('public');

        $siswa = Siswa::create([
            'nisn' => '5555555555',
            'nama_siswa' => 'Ahmad',
        ]);
        $user = User::where('nisn', '5555555555')->first();

        $userAdmin = User::factory()->create(['role' => 'tu']);
        $master = MasterFaktur::create(['nama_faktur' => 'Uang Gedung', 'nominal' => 1000000, 'jenis_faktur' => 'insidental']);
        $faktur = TuFaktur::create([
            'master_faktur_id' => $master->id,
            'target_type' => 'siswa',
            'target_value' => 'Ahmad - 5555555555',
            'status' => 'Berjalan',
            'tersedia_pada' => now()->format('Y-m-d'),
            'jatuh_tempo' => now()->addDays(7)->format('Y-m-d'),
            'created_by' => $userAdmin->id,
        ]);

        $file = UploadedFile::fake()->image('bukti.jpg');

        $response = $this->actingAs($user)->post("/ortu/faktur/{$faktur->id}/submit", [
            'berkas_file' => $file
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('penyerahan_fakturs', [
            'tu_faktur_id' => $faktur->id,
            'siswa_id' => $siswa->id,
        ]);
        
        // Cek path file jika berhasil tersimpan
        $fileUpload = \App\Models\PenyerahanFaktur::first()->berkas_file;
        Storage::disk('public')->assertExists($fileUpload);
    }
}
