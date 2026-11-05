<?php

namespace Tests\Feature;

use App\Features\Admin\Livewire\AdminUserStockList;
use App\Features\Admin\Livewire\UserList;
use App\Features\Profile\Livewire\Profile;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Livewire\StockDetail;
use App\Features\Stock\Livewire\StockList;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockExcelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class StockWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_member_can_view_own_stocks(): void
    {
        $u = User::create(['name' => 'Member', 'username' => 'member1', 'password' => 'password', 'role' => 'member']);
        Stock::create(['user_id' => $u->id, 'name' => 'Mouse', 'quantity' => 4, 'unit' => 'pcs', 'min_stock' => 5, 'status' => StockStatus::LowStock]);
        $this->actingAs($u)->get('/stocks')->assertOk()->assertSee('Mouse');
    }

    public function test_member_cannot_open_another_members_stock(): void
    {
        $a = User::create(['name' => 'A', 'username' => 'a', 'password' => 'password', 'role' => 'member']);
        $b = User::create(['name' => 'B', 'username' => 'b', 'password' => 'password', 'role' => 'member']);
        $stock = Stock::create(['user_id' => $b->id, 'name' => 'Private', 'quantity' => 1, 'unit' => 'pcs', 'min_stock' => 2, 'status' => StockStatus::LowStock]);
        $this->actingAs($a)->get('/stocks/'.$stock->id)->assertNotFound();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/stocks')->assertRedirect('/login');
    }

    public function test_member_can_crud_adjust_cover_markdown_excel_ai_and_profile(): void
    {
        Storage::fake('public');
        $member = User::create(['name' => 'Member', 'username' => 'member', 'password' => 'password', 'role' => 'member']);
        $this->actingAs($member);

        // 1. Create Stock with Cover + Markdown via StockList
        $cover = UploadedFile::fake()->image('laptop.jpg', 400, 300);
        Livewire::test(StockList::class)
            ->call('create')
            ->set('name', 'Laptop ThinkPad')
            ->set('sku', 'TP-001')
            ->set('quantity', 10)
            ->set('min_stock', 3)
            ->set('unit', 'unit')
            ->set('price', 12000000)
            ->set('description', '**Laptop** spesifikasi tinggi')
            ->set('cover', $cover)
            ->call('save')
            ->assertHasNoErrors();

        $stock = Stock::where('user_id', $member->id)->firstOrFail();
        $this->assertNotNull($stock->cover_image_path);
        $this->assertSame(StockStatus::Available, $stock->status);

        // 2. Edit Stock & Adjust Qty
        Livewire::test(StockList::class)
            ->call('edit', $stock->id)
            ->set('name', 'Laptop ThinkPad X1')
            ->call('save')
            ->call('adjust', $stock->id, -8)
            ->assertHasNoErrors();

        $stock->refresh();
        $this->assertSame('Laptop ThinkPad X1', $stock->name);
        $this->assertSame(2, $stock->quantity);
        $this->assertSame(StockStatus::LowStock, $stock->status);

        // 3. StockDetail Markdown & Cover update & Adjust
        Livewire::test(StockDetail::class, ['stock' => $stock])
            ->set('editing', true)
            ->set('description', '# Spesifikasi Baru')
            ->call('saveDescription')
            ->call('adjust', 5)
            ->assertHasNoErrors();

        $stock->refresh();
        $this->assertSame('# Spesifikasi Baru', $stock->description);
        $this->assertSame(7, $stock->quantity);

        // 4. Excel Export & Import
        $excel = app(StockExcelService::class);
        $exportPath = $excel->export($member->id);
        $this->assertFileExists($exportPath);
        $imported = $excel->import($exportPath, $member->id);
        @unlink($exportPath);
        $this->assertGreaterThanOrEqual(1, $imported);

        // 5. AI Stock Generator (fallback without API key)
        Livewire::test(StockList::class)
            ->set('aiPrompt', 'Laboratorium jaringan')
            ->set('aiCount', 2)
            ->call('generate')
            ->assertHasNoErrors()
            ->call('saveAllGenerated');

        $this->assertGreaterThanOrEqual(4, Stock::where('user_id', $member->id)->count());

        // 6. Profile Update
        $photo = UploadedFile::fake()->image('avatar.png', 200, 200);
        Livewire::test(Profile::class)
            ->set('name', 'Member Updated')
            ->set('username', 'member_updated')
            ->set('photo', $photo)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Member Updated', $member->fresh()->name);
        $this->assertNotNull($member->fresh()->profile_photo_path);
    }

    public function test_admin_can_view_users_and_stocks_read_only(): void
    {
        $admin = User::create(['name' => 'Admin', 'username' => 'admin', 'password' => 'password', 'role' => 'admin']);
        $member = User::create(['name' => 'Budi', 'username' => 'budi', 'password' => 'password', 'role' => 'member']);
        Stock::create(['user_id' => $member->id, 'name' => 'Router MikroTik', 'quantity' => 5, 'unit' => 'unit', 'min_stock' => 2, 'status' => StockStatus::Available]);

        $this->actingAs($admin)->get('/admin/users')->assertOk()->assertSee('Budi');
        $this->actingAs($admin)->get('/admin/users/'.$member->id.'/stocks')->assertOk()->assertSee('Router MikroTik');

        Livewire::actingAs($admin)->test(UserList::class)->set('search', 'Budi')->assertSee('budi');
        Livewire::actingAs($admin)->test(AdminUserStockList::class, ['user' => $member])->assertSee('Router MikroTik');
    }
}
