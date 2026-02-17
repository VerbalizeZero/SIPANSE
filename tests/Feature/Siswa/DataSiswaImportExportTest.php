<?php

namespace Tests\Feature\Siswa;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DataSiswaImportExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tu_can_download_student_template(): void
    {
        $tu = User::factory()->tu()->create();

        $response = $this->actingAs($tu)->get(route('tu.siswa.template'));

        $response->assertOk();
        $response->assertHeader('content-disposition');
        $response->assertSee('nisn,nama_siswa', false);
    }

    /** @test */
    public function non_tu_cannot_download_student_template(): void
    {
        $bendahara = User::factory()->bendahara()->create();

        $this->actingAs($bendahara)
            ->get(route('tu.siswa.template'))
            ->assertForbidden();
    }

    /** @test */
    public function tu_can_import_students_from_template_file(): void
    {
        Storage::fake('local');
        $tu = User::factory()->tu()->create();

        $csv = implode("\n", [
            'nisn,nama_siswa,jenis_kelamin,tanggal_lahir,alamat,nama_ortu,no_hp_ortu',
            '1234567890,Budi Santoso,L,2008-01-02,Surabaya,Sri Santoso,081234567890',
            '1234567891,Siti Aminah,P,2008-03-10,Sidoarjo,Ahmad Amin,081111111111',
        ]);

        $file = UploadedFile::fake()->createWithContent('data-siswa.csv', $csv);

        $response = $this->actingAs($tu)
            ->post(route('tu.siswa.import'), [
                'file' => $file,
            ]);

        $response->assertRedirect(route('tu.siswa.index'));
        $this->assertDatabaseHas('siswas', [
            'nisn' => '1234567890',
            'nama_siswa' => 'Budi Santoso',
        ]);
        $this->assertDatabaseHas('siswas', [
            'nisn' => '1234567891',
            'nama_siswa' => 'Siti Aminah',
        ]);
    }

    /** @test */
    public function tu_can_import_students_with_dd_mm_yyyy_birth_date_format(): void
    {
        $tu = User::factory()->tu()->create();

        $csv = implode("\n", [
            'nisn,nama_siswa,tanggal_lahir',
            '1202220438,Ciney,20/02/2005',
        ]);

        $file = UploadedFile::fake()->createWithContent('data-siswa.csv', $csv);

        $response = $this->actingAs($tu)
            ->post(route('tu.siswa.import'), [
                'file' => $file,
            ]);

        $response->assertRedirect(route('tu.siswa.index'));
        $this->assertDatabaseHas('siswas', [
            'nisn' => '1202220438',
            'tanggal_lahir' => '2005-02-20',
        ]);
    }

    /** @test */
    public function tu_cannot_import_invalid_student_file(): void
    {
        $tu = User::factory()->tu()->create();

        $csv = implode("\n", [
            'nisn,nama_siswa,jenis_kelamin,tanggal_lahir,alamat,nama_ortu,no_hp_ortu',
            ',Tanpa NISN,L,2008-01-02,Surabaya,Ortu,0812',
        ]);

        $file = UploadedFile::fake()->createWithContent('invalid-data-siswa.csv', $csv);

        $response = $this->from(route('tu.siswa.index'))
            ->actingAs($tu)
            ->post(route('tu.siswa.import'), [
                'file' => $file,
            ]);

        $response->assertRedirect(route('tu.siswa.index'));
        $response->assertSessionHasErrors('file');
    }
}
