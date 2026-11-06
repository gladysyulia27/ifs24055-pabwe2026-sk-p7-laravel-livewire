<?php

namespace Tests\Unit\Repositories;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_and_paginate_for_user_filters(): void
    {
        $repo = new StockRepository();
        $user = User::factory()->create();

        $s1 = $repo->create([
            'user_id' => $user->id,
            'name' => 'Router Cisco',
            'sku' => 'CSC-01',
            'quantity' => 10,
            'unit' => 'unit',
            'min_stock' => 2,
            'status' => StockStatus::Available,
        ]);

        $repo->create([
            'user_id' => $user->id,
            'name' => 'Switch MikroTik',
            'sku' => 'MKT-02',
            'quantity' => 0,
            'unit' => 'unit',
            'min_stock' => 2,
            'status' => StockStatus::OutOfStock,
        ]);

        $this->assertCount(2, $repo->paginateForUser($user->id, '', ''));
        $this->assertCount(1, $repo->paginateForUser($user->id, 'Cisco', ''));
        $this->assertCount(1, $repo->paginateForUser($user->id, 'MKT-02', ''));
        $this->assertCount(1, $repo->paginateForUser($user->id, '', 'out_of_stock'));

        $updated = $repo->update($s1, ['name' => 'Router Cisco ISR']);
        $this->assertEquals('Router Cisco ISR', $updated->name);

        $repo->delete($s1);
        $this->assertDatabaseMissing('stocks', ['id' => $s1->id]);
    }
}