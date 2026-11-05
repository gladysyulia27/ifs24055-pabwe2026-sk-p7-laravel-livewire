<?php
namespace Tests\Unit;
use PHPUnit\Framework\TestCase;use App\Features\Stock\Enums\StockStatus;
class StockStatusTest extends TestCase {public function test_quantity_maps_to_status():void{$this->assertSame(StockStatus::OutOfStock,StockStatus::fromQuantity(0,5));$this->assertSame(StockStatus::LowStock,StockStatus::fromQuantity(5,5));$this->assertSame(StockStatus::Available,StockStatus::fromQuantity(6,5));}}
