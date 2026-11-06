<?php

namespace Tests\Feature\Admin;

use App\Features\Admin\Livewire\AdminUserStockList;
use App\Features\Admin\Livewire\UserList;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_search_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $alice = User::factory()->create(['name' => 'Alice Inventory', 'username' => 'alice_inv', 'role' => 'member']);
        $bob = User::factory()->create(['name' => 'Bob Warehouse', 'username' => 'bob_wh', 'role' => 'member']);

        $this->actingAs($admin)->get('/admin/users')->assertOk();

        Livewire::actingAs($admin)
            ->test(UserList::class)
            ->assertSee('Alice Inventory')
            ->assertSee('Bob Warehouse')
            ->set('search', 'Alice')
            ->assertSee('Alice Inventory')
            ->assertDontSee('Bob Warehouse');
    }

    public function test_admin_can_view_user_stocks_read_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);
        Stock::factory()->create(['user_id' => $member->id, 'name' => 'Server Rack 42U']);

        $this->actingAs($admin)->get('/admin/users/'.$member->id.'/stocks')->assertOk();

        Livewire::actingAs($admin)
            ->test(AdminUserStockList::class, ['user' => $member])
            ->assertSee('Server Rack 42U');
    }
}