<?php

namespace App\Features\Stock\Livewire;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockExcelService;
use App\Features\Stock\Services\StockService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class StockList extends Component
{
    use WithFileUploads, WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $search = '';
    public string $status = '';
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $sku = '';
    public string $description = '';
    public string $unit = 'pcs';
    public int $quantity = 0;
    public int $min_stock = 5;
    public $price = null;
    public $cover = null;
    public ?string $existingCoverPath = null;
    public $excelFile = null;
    public string $aiPrompt = '';
    public int $aiCount = 5;
    public array $generated = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $s = Stock::where('user_id', auth()->id())->findOrFail($id);
        $this->resetValidation();
        $this->editingId = $s->id;
        $this->name = (string) $s->name;
        $this->sku = (string) ($s->sku ?? '');
        $this->description = (string) ($s->description ?? '');
        $this->unit = (string) ($s->unit ?: 'pcs');
        $this->quantity = (int) $s->quantity;
        $this->min_stock = (int) $s->min_stock;
        $this->price = $s->price;
        $this->cover = null;
        $this->existingCoverPath = $s->cover_image_path;
        $this->showForm = true;
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'sku', 'description', 'price', 'cover', 'existingCoverPath']);
        $this->quantity = 0;
        $this->min_stock = 5;
        $this->unit = 'pcs';
        $this->resetValidation();
    }

    public function save(StockService $service): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:150',
            'sku' => 'nullable|string|max:80',
            'description' => 'nullable|string|max:10000',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:30',
            'price' => 'nullable|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'cover' => 'nullable|image|max:3072',
        ]);

        unset($data['cover']);
        $data['sku'] = trim((string) ($data['sku'] ?? '')) ?: null;
        $data['description'] = trim((string) ($data['description'] ?? '')) ?: null;
        $data['price'] = ($data['price'] !== null && $data['price'] !== '') ? $data['price'] : null;

        if ($this->editingId) {
            $s = Stock::where('user_id', auth()->id())->findOrFail($this->editingId);
            if ($this->cover) {
                if ($s->cover_image_path) {
                    Storage::disk('public')->delete($s->cover_image_path);
                }
                $data['cover_image_path'] = $this->cover->store('stock-covers', 'public');
            }
            $service->update($s, $data);
        } else {
            if ($this->cover) {
                $data['cover_image_path'] = $this->cover->store('stock-covers', 'public');
            }
            $service->create(auth()->id(), $data);
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'Data stok berhasil disimpan.');
    }

    public function adjust(int $id, int $delta, StockService $service): void
    {
        $s = Stock::where('user_id', auth()->id())->findOrFail($id);
        try {
            $service->adjust($s, $delta);
            session()->flash('success', 'Jumlah stok berhasil diperbarui.');
        } catch (\DomainException $e) {
            $this->addError('quantity', $e->getMessage());
        }
    }

    public function delete(int $id, StockService $service): void
    {
        $s = Stock::where('user_id', auth()->id())->findOrFail($id);
        if ($s->cover_image_path) {
            Storage::disk('public')->delete($s->cover_image_path);
        }
        $service->delete($s);
        session()->flash('success', 'Barang berhasil dihapus.');
    }

    public function export(StockExcelService $excel)
    {
        $path = $excel->export(auth()->id());

        return response()->download($path, 'stock-export.xlsx')->deleteFileAfterSend(true);
    }

    public function import(StockExcelService $excel): void
    {
        $this->validate([
            'excelFile' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);
        $path = $this->excelFile->getRealPath();
        $count = $excel->import($path, auth()->id());
        $this->reset('excelFile');
        session()->flash('success', "{$count} barang berhasil diimpor.");
    }

    public function generate(AiClientInterface $ai): void
    {
        $this->validate([
            'aiPrompt' => 'required|string|max:500',
            'aiCount' => 'required|integer|min:1|max:20',
        ]);
        try {
            $this->generated = $ai->generate($this->aiPrompt, $this->aiCount);
        } catch (\Throwable $e) {
            $this->addError('aiPrompt', app()->hasDebugModeEnabled() ? $e->getMessage() : 'AI tidak dapat dihubungi. Periksa konfigurasi API.');
        }
    }

    public function saveGenerated(int $index, StockService $service): void
    {
        $item = $this->generated[$index] ?? null;
        if (! $item) {
            return;
        }
        $data = [
            'name' => $item['name'],
            'sku' => $item['sku'] ?? null,
            'description' => $item['description'] ?? null,
            'quantity' => max(0, (int) ($item['quantity'] ?? 0)),
            'unit' => $item['unit'] ?? 'pcs',
            'price' => isset($item['price']) ? (float) $item['price'] : null,
            'min_stock' => max(0, (int) ($item['min_stock'] ?? 5)),
        ];
        $service->create(auth()->id(), $data);
        unset($this->generated[$index]);
        session()->flash('success', 'Item AI disimpan.');
    }

    public function saveAllGenerated(StockService $service): void
    {
        if (empty($this->generated)) {
            return;
        }
        $saved = 0;
        foreach ($this->generated as $item) {
            $service->create(auth()->id(), [
                'name' => $item['name'],
                'sku' => $item['sku'] ?? null,
                'description' => $item['description'] ?? null,
                'quantity' => max(0, (int) ($item['quantity'] ?? 0)),
                'unit' => $item['unit'] ?? 'pcs',
                'price' => isset($item['price']) ? (float) $item['price'] : null,
                'min_stock' => max(0, (int) ($item['min_stock'] ?? 5)),
            ]);
            $saved++;
        }
        $this->generated = [];
        session()->flash('success', "{$saved} item AI berhasil disimpan.");
    }

    public function render(StockRepositoryInterface $repository)
    {
        $stocks = $repository->paginateForUser(auth()->id(), $this->search, $this->status, 10);

        return view('livewire.stock.stock-list', [
            'stocks' => $stocks,
            'statuses' => StockStatus::cases(),
        ])->layout('components.layouts.app');
    }
}
