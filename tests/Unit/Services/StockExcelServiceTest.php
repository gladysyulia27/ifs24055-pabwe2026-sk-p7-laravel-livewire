<?php

namespace Tests\Unit\Services;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockExcelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StockExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_and_import_with_edge_cases(): void
    {
        $service = new StockExcelService();
        $user = User::factory()->create();
        Stock::factory()->count(2)->create(['user_id' => $user->id]);

        $exportPath = $service->export($user->id);
        $this->assertFileExists($exportPath);
        @unlink($exportPath);

        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->fromArray([
            ['name', 'sku', 'description', 'qty', 'unit', 'price', 'min_stock', 'status'],
            ['name', 'SKU-HEADER', 'Duplicate header skipped', 1, 'pcs', 100, 1, 'available'],
            ['Item Valid', 'SKU-VAL-1', 'Desc', 12, 'box', 45000, 4, 'available'],
            ['Item Invalid Price', 'SKU-INV-1', 'Desc', 5, 'pcs', -500, 2, 'available'],
        ]);

        $tmp = sys_get_temp_dir().'/test-stock-import-'.uniqid().'.xlsx';
        (new Xlsx($book))->save($tmp);

        $importedCount = $service->import($tmp, $user->id);
        @unlink($tmp);

        $this->assertSame(1, $importedCount);
        $this->assertDatabaseHas('stocks', ['user_id' => $user->id, 'name' => 'Item Valid']);
    }
}