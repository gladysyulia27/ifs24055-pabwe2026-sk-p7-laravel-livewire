<?php

namespace App\Features\Stock\Models;

use App\Features\Stock\Enums\StockStatus;
use App\Models\User;
use Database\Factories\StockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    /** @use HasFactory<StockFactory> */
    use HasFactory;

    protected static function newFactory(): StockFactory
    {
        return StockFactory::new();
    }

    protected $fillable = [
        'user_id',
        'name',
        'sku',
        'description',
        'quantity',
        'unit',
        'price',
        'min_stock',
        'status',
        'cover_image_path',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'quantity' => 'integer',
            'min_stock' => 'integer',
            'status' => StockStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function syncStatus(): void
    {
        $this->status = StockStatus::fromQuantity($this->quantity, $this->min_stock);
    }
}
