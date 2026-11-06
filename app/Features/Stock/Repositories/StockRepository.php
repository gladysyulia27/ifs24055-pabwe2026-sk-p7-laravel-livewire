<?php

namespace App\Features\Stock\Repositories;

use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Models\Stock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StockRepository implements StockRepositoryInterface
{
    public function paginateForUser(int $userId, string $search, string $status, int $perPage = 10): LengthAwarePaginator
    {
        return Stock::where('user_id', $userId)
            ->when($search, fn ($q) => $q->where(fn ($x) => $x
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Stock
    {
        return Stock::create($data);
    }

    public function update(Stock $stock, array $data): Stock
    {
        $stock->update($data);

        return $stock->refresh();
    }

    public function delete(Stock $stock): void
    {
        $stock->delete();
    }
}