<?php

namespace Tests\Unit\Services;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Repositories\StockRepository;
use App\Features\Stock\Services\StockService;
use App\Models\User;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_update_adjust_and_delete(): void
    {
        $service = new StockService(new StockRepository());
        $user = User::factory()->create();

        $stock = $service->create($user->id, [
            'name' => 'Webcam HD',
            'sku' => 'WBC-01',
            'quantity' => 5,
            'unit' => 'pcs',
            'min_stock' => 5,
        ]);

        $this->assertSame(StockStatus::LowStock, $stock->status);

        $updated = $service->update($stock, [
            'name' => 'Webcam Full HD',
            'quantity' => 10,
            'min_stock' => 5,
        ]);
        $this->assertSame(StockStatus::Available, $updated->status);

        $adjusted = $service->adjust($stock, -10);
        $this->assertSame(0, $adjusted->quantity);
        $this->assertSame(StockStatus::OutOfStock, $adjusted->status);

        $this->expectException(DomainException::class);
        try {
            $service->adjust($stock, -1);
        } finally {
            $service->delete($stock);
            $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
        }
    }
}