<div>
    <div class="flex flex-wrap items-end justify-between gap-4 mb-7">
        <div>
            <p class="text-sm text-slate-500">Inventaris / Overview</p>
            <h1 class="text-3xl font-bold mt-1">Daftar Stok</h1>
            <p class="text-slate-500 mt-1">Pantau dan kelola persediaan barang milikmu.</p>
        </div>
        <button wire:click="create" class="btn btn-primary">＋ Tambah Barang</button>
    </div>

    <div class="grid sm:grid-cols-3 gap-4 mb-6">
        <div class="panel p-5">
            <p class="text-sm text-slate-500">Total barang</p>
            <p class="text-3xl font-bold mt-2">{{ $stocks->total() }}</p>
        </div>
        <div class="panel p-5">
            <p class="text-sm text-slate-500">Stok menipis</p>
            <p class="text-3xl font-bold text-amber-600 mt-2">{{ auth()->user()->stocks()->where('status', 'low_stock')->count() }}</p>
        </div>
        <div class="panel p-5">
            <p class="text-sm text-slate-500">Stok habis</p>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ auth()->user()->stocks()->where('status', 'out_of_stock')->count() }}</p>
        </div>
    </div>

    @error('quantity')
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 p-3 text-sm">{{ $message }}</div>
    @enderror

    <div class="panel p-5 mb-5">
        <div class="flex flex-wrap items-center gap-3">
            <label for="stock-search" class="sr-only">Cari nama barang atau SKU</label>
            <input id="stock-search" wire:model.live.debounce.300ms="search" class="field flex-1 min-w-48" placeholder="Cari nama barang atau SKU..." aria-label="Cari nama barang atau SKU" title="Cari nama barang atau SKU">
            <label for="stock-status" class="sr-only">Filter status stok</label>
            <select id="stock-status" wire:model.live="status" class="field sm:max-w-52" aria-label="Filter status stok" title="Filter status stok">
                <option value="">Semua status</option>
                @foreach($statuses as $item)
                    <option value="{{ $item->value }}">{{ $item->label() }}</option>
                @endforeach
            </select>
            <button wire:click="export" class="btn btn-muted">↓ Export Excel</button>
            <label for="stock-excel-file" class="sr-only">Pilih file Excel</label>
            <input id="stock-excel-file" type="file" wire:model="excelFile" accept=".xlsx,.xls,.csv" class="field max-w-64" aria-label="Pilih file Excel" title="Pilih file Excel">
            <button wire:click="import" wire:loading.attr="disabled" wire:target="excelFile,import" class="btn btn-muted">
                <span wire:loading.remove wire:target="excelFile,import">↑ Import Excel</span>
                <span wire:loading wire:target="excelFile,import">Memproses...</span>
            </button>
        </div>
        @error('excelFile')
            <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div class="panel overflow-hidden">
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>SKU</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        <tr wire:key="stock-row-{{ $stock->id }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($stock->cover_image_path)
                                        <img src="/storage/{{ $stock->cover_image_path }}" alt="{{ $stock->name }}" class="w-11 h-11 rounded-lg object-cover border border-slate-200 shrink-0">
                                    @else
                                        <div class="w-11 h-11 rounded-lg bg-slate-100 grid place-items-center text-slate-600 text-xs font-bold shrink-0">IMG</div>
                                    @endif
                                    <div>
                                        <a href="/stocks/{{ $stock->id }}" class="font-semibold text-slate-800 hover:text-blue-700">{{ $stock->name }}</a>
                                        <p class="text-xs text-slate-600">Min: {{ $stock->min_stock }} {{ $stock->unit }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm text-slate-500">{{ $stock->sku ?: '—' }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="adjust({{ $stock->id }}, -1)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 font-bold" aria-label="Kurangi jumlah {{ $stock->name }}">−</button>
                                    <b>{{ $stock->quantity }}</b>
                                    <span class="text-xs text-slate-600">{{ $stock->unit }}</span>
                                    <button type="button" wire:click="adjust({{ $stock->id }}, 1)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 font-bold" aria-label="Tambah jumlah {{ $stock->name }}">+</button>
                                </div>
                            </td>
                            <td class="text-sm">{{ $stock->price !== null ? 'Rp '.number_format((float) $stock->price, 0, ',', '.') : '—' }}</td>
                            <td>
                                <span class="badge {{ $stock->status->badgeClass() }}">{{ $stock->status->label() }}</span>
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <a href="/stocks/{{ $stock->id }}" class="text-slate-600 hover:text-blue-700 text-sm font-medium">Detail</a>
                                    <button type="button" wire:click="edit({{ $stock->id }})" class="text-blue-700 text-sm font-semibold">Edit</button>
                                    <button type="button" wire:click="delete({{ $stock->id }})" wire:confirm="Hapus barang ini?" class="text-red-600 text-sm">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-500">Belum ada barang. Tambahkan barang pertama.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $stocks->links() }}</div>
    </div>

    <div class="panel p-5 mt-6">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <div>
                <h2 class="font-bold">AI Stock Generator</h2>
                <p class="text-sm text-slate-500">Buat draft item inventaris otomatis, tinjau, lalu simpan item yang dibutuhkan.</p>
            </div>
            @if($generated)
                <button type="button" wire:click="saveAllGenerated" class="btn btn-primary text-sm">Simpan Semua ({{ count($generated) }})</button>
            @endif
        </div>
        <div class="flex flex-wrap gap-3">
            <label for="ai-prompt" class="sr-only">Prompt AI Stock Generator</label>
            <input id="ai-prompt" wire:model="aiPrompt" class="field flex-1" placeholder="Contoh: inventaris perlengkapan laboratorium komputer" aria-label="Prompt AI Stock Generator" title="Prompt AI Stock Generator">
            <label for="ai-count" class="sr-only">Jumlah item AI</label>
            <input id="ai-count" type="number" wire:model="aiCount" min="1" max="20" class="field w-24" placeholder="Jumlah" aria-label="Jumlah item AI" title="Jumlah item AI">
            <button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate" class="btn btn-primary">
                <span wire:loading.remove wire:target="generate">Generate</span>
                <span wire:loading wire:target="generate">Generating...</span>
            </button>
        </div>
        @error('aiPrompt')
            <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
        @enderror
        @error('aiCount')
            <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
        @enderror

        @if($generated)
            <div class="grid md:grid-cols-2 gap-3 mt-4">
                @foreach($generated as $i => $item)
                    <div wire:key="ai-item-{{ $i }}" class="border rounded-xl p-4 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <b>{{ $item['name'] }}</b>
                                @if(!empty($item['sku']))
                                    <span class="text-xs text-slate-600 font-mono">{{ $item['sku'] }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-500 mt-1">{{ $item['description'] ?? '' }}</p>
                            <p class="text-xs text-slate-600 mt-2">
                                Qty: <b>{{ $item['quantity'] ?? 0 }} {{ $item['unit'] ?? 'pcs' }}</b> ·
                                Min: <b>{{ $item['min_stock'] ?? 5 }}</b>
                                @if(!empty($item['price']))
                                    · Rp {{ number_format((float) $item['price'], 0, ',', '.') }}
                                @endif
                            </p>
                        </div>
                        <button type="button" wire:click="saveGenerated({{ $i }})" class="btn btn-muted mt-3 w-fit">Simpan item</button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($showForm)
        <div class="fixed inset-0 bg-slate-950/40 z-40 grid place-items-center p-4">
            <div class="panel w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-5">
                    <h2 class="text-xl font-bold">{{ $editingId ? 'Edit Barang' : 'Tambah Barang' }}</h2>
                    <button type="button" wire:click="$set('showForm', false)" class="text-slate-500 hover:text-slate-800" aria-label="Tutup form">✕</button>
                </div>
                <form wire:submit="save" class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="form-stock-name" class="text-sm font-semibold">Nama barang</label>
                        <input id="form-stock-name" wire:model="name" class="field mt-1" placeholder="Contoh: Laptop Lenovo" aria-label="Nama barang" title="Nama barang">
                        @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="form-stock-sku" class="text-sm font-semibold">SKU</label>
                        <input id="form-stock-sku" wire:model="sku" class="field mt-1" placeholder="Contoh: SKU-EL-001" aria-label="SKU" title="SKU">
                        @error('sku')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="form-stock-unit" class="text-sm font-semibold">Satuan</label>
                        <input id="form-stock-unit" wire:model="unit" class="field mt-1" placeholder="pcs / unit / box" aria-label="Satuan" title="Satuan">
                        @error('unit')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="form-stock-quantity" class="text-sm font-semibold">Quantity</label>
                        <input id="form-stock-quantity" type="number" min="0" wire:model="quantity" class="field mt-1" placeholder="0" aria-label="Quantity" title="Quantity">
                        @error('quantity')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="form-stock-min" class="text-sm font-semibold">Minimum stock</label>
                        <input id="form-stock-min" type="number" min="0" wire:model="min_stock" class="field mt-1" placeholder="0" aria-label="Minimum stock" title="Minimum stock">
                        @error('min_stock')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="form-stock-price" class="text-sm font-semibold">Harga (Rp)</label>
                        <input id="form-stock-price" type="number" min="0" step="0.01" wire:model="price" class="field mt-1" placeholder="Opsional" aria-label="Harga (Rp)" title="Harga (Rp)">
                        @error('price')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="form-stock-cover" class="text-sm font-semibold">Cover Image (Opsional)</label>
                        <input id="form-stock-cover" type="file" wire:model="cover" accept="image/*" class="field mt-1" aria-label="Cover Image (Opsional)" title="Cover Image (Opsional)">
                        @if($existingCoverPath && !$cover)
                            <p class="text-xs text-slate-500 mt-1">Cover saat ini tersimpan. Unggah file baru untuk mengganti.</p>
                        @endif
                        @error('cover')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="form-stock-description" class="text-sm font-semibold">Deskripsi (Markdown)</label>
                        <textarea id="form-stock-description" wire:model="description" rows="4" class="field mt-1 font-mono text-sm" placeholder="Gunakan format **tebal**, *miring*, atau - list item..." aria-label="Deskripsi (Markdown)" title="Deskripsi (Markdown)"></textarea>
                        @error('description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showForm', false)" class="btn btn-muted">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save,cover" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>