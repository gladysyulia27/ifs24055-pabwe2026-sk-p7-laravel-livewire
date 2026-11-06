<?php

namespace Tests\Unit\Models;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_roles_and_stocks_relationship(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->unverified()->create(['role' => 'member']);
        Stock::factory()->count(2)->create(['user_id' => $member->id]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isMember());

        $this->assertTrue($member->isMember());
        $this->assertFalse($member->isAdmin());
        $this->assertNull($member->email_verified_at);
        $this->assertCount(2, $member->stocks);
    }
}