<?php
namespace App\Features\Ai\DTO;
class GeneratedStockItem {public function __construct(public string $name,public ?string $sku,public string $description,public int $quantity,public string $unit,public ?float $price,public int $minStock){}public function toArray():array{return ['name'=>$this->name,'sku'=>$this->sku,'description'=>$this->description,'quantity'=>$this->quantity,'unit'=>$this->unit,'price'=>$this->price,'min_stock'=>$this->minStock];}}
