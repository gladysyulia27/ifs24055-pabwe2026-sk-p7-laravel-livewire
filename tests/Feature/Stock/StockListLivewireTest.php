<?php

namespace Tests\Feature\Stock;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Stock\Livewire\StockList;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Tests\TestCase;

class StockListLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_filters_adjust_and_cover_replacement(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'member']);

        $cover1 = UploadedFile::fake()->image('item1.jpg');
        $cover2 = UploadedFile::fake()->image('item2.jpg');

        $component = Livewire::actingAs($user)
            ->test(StockList::class)
            ->set('search', 'cari')
            ->set('status', 'available')
            ->set('search', '')
            ->set('status', '')
            ->call('create')
            ->set('name', 'Monitor LG')
            ->set('sku', 'LG-01')
            ->set('quantity', 6)
            ->set('min_stock', 2)
            ->set('unit', 'unit')
            ->set('price', 2000000)
            ->set('description', 'Deskripsi monitor')
            ->set('cover', $cover1)
            ->call('save')
            ->assertHasNoErrors();

        $stock = Stock::where('user_id', $user->id)->firstOrFail();
        $oldCover = $stock->cover_image_path;
        Storage::disk('public')->assertExists($oldCover);

        // Edit and replace cover
        $component
            ->call('edit', $stock->id)
            ->set('name', 'Monitor LG UltraGear')
            ->set('cover', $cover2)
            ->call('save')
            ->assertHasNoErrors();

        $stock->refresh();
        $this->assertEquals('Monitor LG UltraGear', $stock->name);
        Storage::disk('public')->assertMissing($oldCover);
        Storage::disk('public')->assertExists($stock->cover_image_path);

        // Adjust valid and invalid
        $component
            ->call('adjust', $stock->id, -1)
            ->assertHasNoErrors()
            ->call('adjust', $stock->id, -999)
            ->assertHasErrors(['quantity']);

        // Delete removes cover and record
        $currentCover = $stock->fresh()->cover_image_path;
        $component->call('delete', $stock->id);
        Storage::disk('public')->assertMissing($currentCover);
        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_export_import_and_ai_generation(): void
    {
        $user = User::factory()->create(['role' => 'member']);
        Stock::factory()->create(['user_id' => $user->id, 'name' => 'Barang Export']);

        $component = Livewire::actingAs($user)->test(StockList::class);

        // Export
        $response = $component->call('export');
        $response->assertFileDownloaded('stock-export.xlsx');

        // Prepare Excel file for import
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->fromArray([
            ['name', 'sku', 'description', 'qty', 'unit', 'price', 'min_stock', 'status'],
            ['Barang Import Livewire', 'IMP-01', 'Ket', 10, 'pcs', 50000, 3, 'available'],
        ]);
        $tmpPath = sys_get_temp_dir().'/lw-import-'.uniqid().'.xlsx';
        (new Xlsx($book))->save($tmpPath);
        $fileContent = file_get_contents($tmpPath);
        @unlink($tmpPath);

        $uploadedExcel = UploadedFile::fake()->createWithContent('stocks.xlsx', $fileContent);

        $component
            ->set('excelFile', $uploadedExcel)
            ->call('import')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stocks', ['user_id' => $user->id, 'name' => 'Barang Import Livewire']);

        // AI generation and saving single / all / empty / invalid index
        $component
            ->call('saveAllGenerated')
            ->call('saveGenerated', 999)
            ->set('aiPrompt', 'Perlengkapan jaringan')
            ->set('aiCount', 3)
            ->call('generate')
            ->assertHasNoErrors()
            ->call('saveGenerated', 0)
            ->call('saveAllGenerated');

        $this->assertGreaterThanOrEqual(5, Stock::where('user_id', $user->id)->count());

        // AI failure handling
        $this->app->bind(AiClientInterface::class, fn () => new class implements AiClientInterface {
            public function generate(string $prompt, int $count = 5): array
            {
                throw new RuntimeException('Simulasi AI Gagal');
            }
        });

        Livewire::actingAs($user)
            ->test(StockList::class)
            ->set('aiPrompt', 'Test Error')
            ->set('aiCount', 2)
            ->call('generate')
            ->assertHasErrors(['aiPrompt']);
    }
}