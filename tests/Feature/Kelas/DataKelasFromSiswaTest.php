<?php

namespace Tests\Feature\Kelas;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataKelasFromSiswaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tu_can_access_data_kelas_index_page(): void
    {
        $tu = User::factory()->tu()->create();

        $this->actingAs($tu)
            ->get(route('tu.kelas.index'))
            ->assertOk();
    }

    /** @test */
    public function non_tu_cannot_access_data_kelas_index_page(): void
    {
        $bendahara = User::factory()->bendahara()->create();

        $this->actingAs($bendahara)
            ->get(route('tu.kelas.index'))
            ->assertForbidden();
    }

    /** @test */
    public function guest_is_redirected_when_accessing_data_kelas_index_page(): void
    {
        $this->get(route('tu.kelas.index'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function data_kelas_is_derived_from_siswa_table_and_grouped_per_kelas(): void
    {
        $tu = User::factory()->tu()->create();

        Siswa::create([
            'nisn' => '1000000001',
            'nama_siswa' => 'Siswa XA 1',
            'kelas' => 'X-A',
        ]);

        Siswa::create([
            'nisn' => '1000000002',
            'nama_siswa' => 'Siswa XA 2',
            'kelas' => 'X-A',
        ]);

        Siswa::create([
            'nisn' => '1000000003',
            'nama_siswa' => 'Siswa XB 1',
            'kelas' => 'X-B',
        ]);

        Siswa::create([
            'nisn' => '1000000004',
            'nama_siswa' => 'Tanpa Kelas',
            'kelas' => null,
        ]);

        $response = $this->actingAs($tu)->get(route('tu.kelas.index'));

        $response->assertOk();
        $response->assertSeeText('Data Kelas');
        $response->assertSeeText('X-A');
        $response->assertSeeText('X-B');
        $response->assertSeeText('2 Siswa');
        $response->assertSeeText('1 Siswa');
        $response->assertDontSeeText('Tanpa Kelas');
    }
}
