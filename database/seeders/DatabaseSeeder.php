<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Enums\StockStatus;
class DatabaseSeeder extends Seeder {
    public function run():void{
        $admin=User::updateOrCreate(['username'=>'admin'],['name'=>'Administrator','email'=>'admin@example.com','password'=>'password','role'=>'admin']);$member=User::updateOrCreate(['username'=>'member'],['name'=>'Demo Member','email'=>'member@example.com','password'=>'password','role'=>'member']);if($member->stocks()->count()===0){foreach([['Laptop Lenovo','SKU-EL-001',12,'unit',5,7500000],['Mouse Wireless','SKU-AC-002',3,'pcs',5,125000],['Keyboard Mechanical','SKU-AC-003',0,'pcs',5,450000],['Monitor 24 inch','SKU-MN-004',8,'unit',3,1850000]] as [$name,$sku,$qty,$unit,$min,$price])Stock::create(['user_id'=>$member->id,'name'=>$name,'sku'=>$sku,'quantity'=>$qty,'unit'=>$unit,'min_stock'=>$min,'price'=>$price,'description'=>'Contoh data inventaris untuk demonstrasi.','status'=>StockStatus::fromQuantity($qty,$min)]);}}}
