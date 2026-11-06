<?php

namespace Tests\Unit\Policies;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Policies\StockPolicy;
use App\Models\User;
use Tests\TestCase;

class StockPolicyTest extends TestCase
{
    public function test_policy_permissions_for_admin_owner_and_other_member(): void
    {
        $policy = new StockPolicy();

        $admin = new User(['role' => 'admin']);
        $admin->id = 1;

        $owner = new User(['role' => 'member']);
        $owner->id = 2;

        $other = new User(['role' => 'member']);
        $other->id = 3;

        $stock = new Stock(['user_id' => 2]);

        $this->assertTrue($policy->view($admin, $stock));
        $this->assertFalse($policy->update($admin, $stock));
        $this->assertFalse($policy->delete($admin, $stock));

        $this->assertTrue($policy->view($owner, $stock));
        $this->assertTrue($policy->update($owner, $stock));
        $this->assertTrue($policy->delete($owner, $stock));

        $this->assertFalse($policy->view($other, $stock));
        $this->assertFalse($policy->update($other, $stock));
        $this->assertFalse($policy->delete($other, $stock));
    }
}