<?php

namespace Tests\Feature;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_guest_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_root_redirects_member_to_stocks(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)->get('/')->assertRedirect('/stocks');
    }

    public function test_root_redirects_admin_to_admin_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/')->assertRedirect('/admin/users');
    }

    public function test_logout_route_logs_out_and_redirects(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_member_cannot_access_admin_routes(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)->get('/admin/users')->assertForbidden();
    }

    public function test_member_cannot_view_other_member_stock_detail(): void
    {
        $owner = User::factory()->create(['role' => 'member']);
        $other = User::factory()->create(['role' => 'member']);
        $stock = Stock::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)->get('/stocks/'.$stock->id)->assertNotFound();
    }
}