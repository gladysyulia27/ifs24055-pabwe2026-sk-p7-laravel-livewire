<?php

namespace Tests\Feature\Stock;

use App\Features\Stock\Livewire\StockDetail;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class StockDetailLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_detail_markdown_cover_and_adjust(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'member']);
        $stock = Stock::factory()->create([
            'user_id' => $user->id,
            'quantity' => 2,
            'min_stock' => 5,
            'description' => 'Awal',
        ]);

        $this->actingAs($user)->get('/stocks/'.$stock->id)->assertOk();

        $cover1 = UploadedFile::fake()->image('cover1.jpg', 300, 200);
        $cover2 = UploadedFile::fake()->image('cover2.jpg', 300, 200);

        Livewire::actingAs($user)
            ->test(StockDetail::class, ['stock' => $stock])
            ->set('editing', true)
            ->set('description', '# Judul Markdown')
            ->call('saveDescription')
            ->assertSet('editing', false)
            ->set('cover', $cover1)
            ->call('saveCover')
            ->assertHasNoErrors();

        $stock->refresh();
        $firstCover = $stock->cover_image_path;
        $this->assertEquals('# Judul Markdown', $stock->description);
        $this->assertNotNull($firstCover);
        Storage::disk('public')->assertExists($firstCover);

        // Replace cover, adjust valid and invalid delta
        Livewire::actingAs($user)
            ->test(StockDetail::class, ['stock' => $stock])
            ->set('cover', $cover2)
            ->call('saveCover')
            ->call('adjust', 3)
            ->assertHasNoErrors()
            ->call('adjust', -100)
            ->assertHasErrors(['quantity']);

        $stock->refresh();
        $this->assertEquals(5, $stock->quantity);
        Storage::disk('public')->assertMissing($firstCover);
        Storage::disk('public')->assertExists($stock->cover_image_path);
    }
}