<?php

namespace Tests\Unit\DTO;

use App\Features\Ai\DTO\GeneratedStockItem;
use PHPUnit\Framework\TestCase;

class GeneratedStockItemTest extends TestCase
{
    public function test_dto_to_array(): void
    {
        $dto = new GeneratedStockItem(
            name: 'Kabel LAN Cat6',
            sku: 'LAN-006',
            description: 'Kabel UTP 305m',
            quantity: 15,
            unit: 'roll',
            price: 1250000.0,
            minStock: 3,
        );

        $this->assertSame([
            'name' => 'Kabel LAN Cat6',
            'sku' => 'LAN-006',
            'description' => 'Kabel UTP 305m',
            'quantity' => 15,
            'unit' => 'roll',
            'price' => 1250000.0,
            'min_stock' => 3,
        ], $dto->toArray());
    }
}