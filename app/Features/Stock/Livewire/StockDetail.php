<?php

namespace App\Features\Stock\Livewire;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class StockDetail extends Component
{
    use WithFileUploads;

    public Stock $stock;
    public $cover = null;
    public string $description = '';
    public bool $editing = false;

    public function mount(Stock $stock): void
    {
        abort_unless($stock->user_id === auth()->id(), 404);
        $this->stock = $stock;
        $this->description = (string) ($stock->description ?? '');
    }

    public function saveDescription(): void
    {
        $this->validate([
            'description' => 'nullable|string|max:10000',
        ]);
        $this->stock->update(['description' => $this->description]);
        $this->editing = false;
        session()->flash('success', 'Deskripsi diperbarui.');
    }

    public function saveCover(): void
    {
        $this->validate([
            'cover' => 'required|image|max:3072',
        ]);
        if ($this->stock->cover_image_path) {
            Storage::disk('public')->delete($this->stock->cover_image_path);
        }
        $path = $this->cover->store('stock-covers', 'public');
        $this->stock->update(['cover_image_path' => $path]);
        $this->stock->refresh();
        $this->reset('cover');
        session()->flash('success', 'Cover berhasil diperbarui.');
    }

    public function adjust(int $delta, StockService $service): void
    {
        try {
            $service->adjust($this->stock, $delta);
            $this->stock->refresh();
            session()->flash('success', 'Jumlah stok berhasil diperbarui.');
        } catch (\DomainException $e) {
            $this->addError('quantity', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.stock.stock-detail')->layout('components.layouts.app');
    }
}
