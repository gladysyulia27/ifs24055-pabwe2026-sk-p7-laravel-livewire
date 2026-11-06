<?php

namespace App\Features\Stock\Services;

use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function __construct(private StockRepositoryInterface $stocks) {}

    public function create(int $userId, array $data): Stock
    {
        $data['user_id'] = $userId;
        $data['status'] = StockStatus::fromQuantity((int) $data['quantity'], (int) $data['min_stock']);

        return $this->stocks->create($data);
    }

    public function update(Stock $stock, array $data): Stock
    {
        $data['status'] = StockStatus::fromQuantity((int) $data['quantity'], (int) $data['min_stock']);

        return $this->stocks->update($stock, $data);
    }

    public function adjust(Stock $stock, int $delta): Stock
    {
        return DB::transaction(function () use ($stock, $delta) {
            $locked = Stock::whereKey($stock->id)->lockForUpdate()->firstOrFail();
            $next = $locked->quantity + $delta;
            if ($next < 0) {
                throw new \DomainException('Jumlah stok tidak boleh kurang dari nol.');
            }
            $locked->quantity = $next;
            $locked->syncStatus();
            $locked->save();

            return $locked->refresh();
        });
    }

    public function delete(Stock $stock): void
    {
        $this->stocks->delete($stock);
    }
}