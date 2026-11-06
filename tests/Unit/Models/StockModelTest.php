<?php

namespace Tests\Unit\Models;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_belongs_to_user_and_syncs_status(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->create([
            'user_id' => $user->id,
            'quantity' => 0,
            'min_stock' => 5,
        ]);

        $this->assertInstanceOf(User::class, $stock->user);
        $this->assertEquals($user->id, $stock->user->id);

        $stock->quantity = 8;
        $stock->syncStatus();
        $this->assertSame(StockStatus::Available, $stock->status);
    }
}