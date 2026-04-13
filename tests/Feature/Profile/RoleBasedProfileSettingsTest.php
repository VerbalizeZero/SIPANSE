<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_tu_can_open_role_profile_page(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);

        $this->actingAs($tu)
            ->get(route('tu.profile.edit'))
            ->assertOk()
            ->assertViewIs('tu.profile.edit');
    }

    public function test_bendahara_can_open_role_profile_page(): void
    {
        $bendahara = User::factory()->create(['role' => 'bendahara']);

        $this->actingAs($bendahara)
            ->get(route('bendahara.profile.edit'))
            ->assertOk()
            ->assertViewIs('bendahara.profile.edit');
    }

    public function test_tu_can_update_profile_and_set_pic_toggle(): void
    {
        $tu = User::factory()->create([
            'role' => 'tu',
            'name' => 'TU Lama',
            'email' => 'tu-lama@example.com',
        ]);

        $response = $this->actingAs($tu)->put(route('tu.profile.update'), [
            'name' => 'TU Baru',
            'contact' => '081234567890',
            'is_pic' => '1',
        ]);

        $response->assertRedirect(route('tu.profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $tu->id,
            'name' => 'TU Baru',
            'contact' => '081234567890',
            'is_pic' => 1,
        ]);
    }

    public function test_only_one_tu_can_be_pic(): void
    {
        $tuA = User::factory()->create([
            'role' => 'tu',
            'name' => 'TU A',
            'email' => 'tu-a@example.com',
        ]);

        $tuB = User::factory()->create([
            'role' => 'tu',
            'name' => 'TU B',
            'email' => 'tu-b@example.com',
        ]);

        $this->actingAs($tuA)->put(route('tu.profile.update'), [
            'name' => 'TU A',
            'contact' => '081100000001',
            'is_pic' => '1',
        ]);

        $this->actingAs($tuB)->put(route('tu.profile.update'), [
            'name' => 'TU B',
            'contact' => '081100000002',
            'is_pic' => '1',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $tuA->id,
            'is_pic' => 0,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $tuB->id,
            'is_pic' => 1,
        ]);
    }

    public function test_ortu_navbar_hides_profile_menu_and_uses_ortu_logout(): void
    {
        $ortu = User::factory()->create([
            'role' => 'orang_tua',
            'nisn' => '1200999988',
        ]);

        \App\Models\Siswa::create([
            'nisn' => '1200999988',
            'nama_siswa' => 'Siswa Ortu',
            'tahun_angkatan' => '2027',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl Test',
            'nama_ortu' => 'Nama Ortu',
            'no_hp_ortu' => '0812000000',
            'kelas' => 'A',
        ]);

        $response = $this->actingAs($ortu)->get(route('ortu.dashboard'));

        $response->assertOk();
        $response->assertDontSee('href="'.route('tu.profile.edit', absolute: false).'"', false);
        $response->assertDontSee('href="'.route('bendahara.profile.edit', absolute: false).'"', false);
        $response->assertSee('action="'.route('ortu.logout').'"', false);
        $response->assertDontSee('action="'.route('logout').'"', false);
    }
}

