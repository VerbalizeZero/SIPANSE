<?php

namespace Tests\Feature\Kelas;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataKelasPromoteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tu_can_preview_mass_promotion_impact(): void
    {
        $tu = User::factory()->tu()->create();

        Siswa::create(['nisn' => '1200000001', 'nama_siswa' => 'A1', 'kelas' => 'X-A']);
        Siswa::create(['nisn' => '1200000002', 'nama_siswa' => 'A2', 'kelas' => 'X-A']);
        Siswa::create(['nisn' => '1200000003', 'nama_siswa' => 'B1', 'kelas' => 'X-B']);

        $response = $this->actingAs($tu)
            ->postJson(route('tu.kelas.promote.preview'), [
                'mappings' => [
                    ['tahun_angkatan_raw' => '__NULL__', 'kelas' => 'X-A', 'level' => '11'],
                    ['tahun_angkatan_raw' => '__NULL__', 'kelas' => 'X-B', 'level' => '11'],
                ],
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.total_affected', 3);
        $response->assertJsonPath('data.details.0.kelas', 'X-A');
        $response->assertJsonPath('data.details.0.level', '11');
        $response->assertJsonPath('data.details.0.affected', 2);
    }

    /** @test */
    public function non_tu_cannot_preview_mass_promotion(): void
    {
        $bendahara = User::factory()->bendahara()->create();

        $this->actingAs($bendahara)
            ->postJson(route('tu.kelas.promote.preview'), [
                'mappings' => [
                    ['tahun_angkatan_raw' => '__NULL__', 'kelas' => 'X-A', 'level' => '11'],
                ],
            ])
            ->assertForbidden();
    }

    /** @test */
    public function tu_can_execute_mass_promotion(): void
    {
        $tu = User::factory()->tu()->create();

        Siswa::create(['nisn' => '1300000001', 'nama_siswa' => 'C1', 'kelas' => 'X-A']);
        Siswa::create(['nisn' => '1300000002', 'nama_siswa' => 'C2', 'kelas' => 'X-A']);
        Siswa::create(['nisn' => '1300000003', 'nama_siswa' => 'D1', 'kelas' => 'X-B']);

        $response = $this->actingAs($tu)
            ->post(route('tu.kelas.promote.execute'), [
                'mappings' => [
                    ['tahun_angkatan_raw' => '__NULL__', 'kelas' => 'X-A', 'level' => '11'],
                    ['tahun_angkatan_raw' => '__NULL__', 'kelas' => 'X-B', 'level' => '11'],
                ],
            ]);

        $response->assertRedirect(route('tu.kelas.index'));

        $this->assertDatabaseCount('siswas', 3);
        $this->assertDatabaseHas('siswas', ['nisn' => '1300000001', 'kelas' => 'X-A']);
        $this->assertDatabaseHas('siswas', ['nisn' => '1300000002', 'kelas' => 'X-A']);
        $this->assertDatabaseHas('siswas', ['nisn' => '1300000003', 'kelas' => 'X-B']);

        $this->assertDatabaseHas('data_kelas', ['tahun_angkatan' => '__NULL__', 'kelas' => 'X-A', 'level' => '11']);
        $this->assertDatabaseHas('data_kelas', ['tahun_angkatan' => '__NULL__', 'kelas' => 'X-B', 'level' => '11']);
    }

    /** @test */
    public function invalid_mapping_rolls_back_all_changes(): void
    {
        $tu = User::factory()->tu()->create();

        Siswa::create(['nisn' => '1400000001', 'nama_siswa' => 'E1', 'kelas' => 'X-A']);
        Siswa::create(['nisn' => '1400000002', 'nama_siswa' => 'E2', 'kelas' => 'X-B']);

        $response = $this->from(route('tu.kelas.index'))
            ->actingAs($tu)
            ->post(route('tu.kelas.promote.execute'), [
                'mappings' => [
                    ['tahun_angkatan_raw' => '__NULL__', 'kelas' => 'X-A', 'level' => '11'],
                    ['tahun_angkatan_raw' => '', 'kelas' => 'X-B', 'level' => 'Graduated'],
                ],
            ]);

        $response->assertRedirect(route('tu.kelas.index'));
        $response->assertSessionHasErrors('mappings.1.tahun_angkatan_raw');

        // Transaction expectation: jika ada mapping invalid, tidak ada perubahan sama sekali.
        $this->assertDatabaseHas('siswas', ['nisn' => '1400000001', 'kelas' => 'X-A']);
        $this->assertDatabaseHas('siswas', ['nisn' => '1400000002', 'kelas' => 'X-B']);
    }
}
