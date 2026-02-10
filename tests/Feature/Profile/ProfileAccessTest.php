<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function profile_page_requires_authentication(): void
    {
        $this->get(route('profile.edit'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_view_profile_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertStatus(200);
    }
}
