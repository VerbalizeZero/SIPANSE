<?php

namespace Tests\Feature\Authorization;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tu_can_access_tu_area(): void
    {
        $tu = User::factory()->tu()->create();
        $this->actingAs($tu)->get('/tu')->assertOk();
    }

    /** @test */
    public function bendahara_cannot_access_tu_area(): void
    {
        $bendahara = User::factory()->bendahara()->create();
        $this->actingAs($bendahara)->get('/tu')->assertStatus(403);
    }

    /** @test */
    public function ortu_can_access_ortu_area(): void
    {
        $ortu = User::factory()->ortu()->create();
        $this->actingAs($ortu)->get('/ortu')->assertOk();
    }

    /** @test */
    public function tu_cannot_access_bendahara_area(): void
    {
        $tu = User::factory()->tu()->create();
        $this->actingAs($tu)->get('/bendahara')->assertStatus(403);
    }

    /** @test */
    public function guest_redirected_when_accessing_protected_area(): void
    {
        $this->get('/tu')->assertRedirect(route('login'));
    }
}
