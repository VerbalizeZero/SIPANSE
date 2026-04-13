<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tu_profile_page_requires_authentication(): void
    {
        $this->get(route('tu.profile.edit'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function bendahara_profile_page_requires_authentication(): void
    {
        $this->get(route('bendahara.profile.edit'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_tu_can_view_tu_profile_page(): void
    {
        $tu = User::factory()->create(['role' => 'tu']);

        $this->actingAs($tu)
            ->get(route('tu.profile.edit'))
            ->assertStatus(200);
    }

    /** @test */
    public function authenticated_bendahara_can_view_bendahara_profile_page(): void
    {
        $bendahara = User::factory()->create(['role' => 'bendahara']);

        $this->actingAs($bendahara)
            ->get(route('bendahara.profile.edit'))
            ->assertStatus(200);
    }
}
