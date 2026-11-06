<?php

namespace Tests\Unit\Services;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_and_stocks_for_user(): void
    {
        $service = new AdminUserService();
        $member1 = User::factory()->create(['name' => 'Andi Pratama', 'username' => 'andi_p', 'email' => 'andi@test.com', 'role' => 'member']);
        User::factory()->create(['name' => 'Budi Santoso', 'username' => 'budi_s', 'email' => 'budi@test.com', 'role' => 'member']);
        Stock::factory()->count(3)->create(['user_id' => $member1->id]);

        $this->assertCount(2, $service->users());
        $this->assertCount(1, $service->users('Andi'));
        $this->assertCount(1, $service->users('budi_s'));
        $this->assertCount(1, $service->users('budi@test.com'));
        $this->assertCount(3, $service->stocksFor($member1));
    }
}