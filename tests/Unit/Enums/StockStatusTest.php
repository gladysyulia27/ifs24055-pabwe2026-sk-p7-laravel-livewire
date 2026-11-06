<?php

namespace Tests\Unit\Enums;

use App\Features\Stock\Enums\StockStatus;
use PHPUnit\Framework\TestCase;

class StockStatusTest extends TestCase
{
    public function test_labels_badges_and_from_quantity(): void
    {
        $this->assertSame('Tersedia', StockStatus::Available->label());
        $this->assertSame('Stok Menipis', StockStatus::LowStock->label());
        $this->assertSame('Habis', StockStatus::OutOfStock->label());

        $this->assertSame('badge-green', StockStatus::Available->badgeClass());
        $this->assertSame('badge-yellow', StockStatus::LowStock->badgeClass());
        $this->assertSame('badge-red', StockStatus::OutOfStock->badgeClass());

        $this->assertSame(StockStatus::OutOfStock, StockStatus::fromQuantity(0, 5));
        $this->assertSame(StockStatus::LowStock, StockStatus::fromQuantity(3, 5));
        $this->assertSame(StockStatus::Available, StockStatus::fromQuantity(10, 5));
    }
}