<?php
namespace App\Features\Stock\Models;
use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Factories\HasFactory;use Illuminate\Database\Eloquent\Relations\BelongsTo;use App\Features\Stock\Enums\StockStatus;
class Stock extends Model {use HasFactory;protected $fillable=['user_id','name','sku','description','quantity','unit','price','min_stock','status','cover_image_path'];protected function casts():array{return ['price'=>'decimal:2','quantity'=>'integer','min_stock'=>'integer','status'=>StockStatus::class];}public function user():BelongsTo{return $this->belongsTo(\App\Models\User::class);}public function syncStatus():void{$this->status=StockStatus::fromQuantity($this->quantity,$this->min_stock);}}
