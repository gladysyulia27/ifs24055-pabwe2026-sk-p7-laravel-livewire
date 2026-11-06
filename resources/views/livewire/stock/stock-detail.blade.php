<div>
    <a href="/stocks" class="text-sm text-blue-700 font-medium">← Kembali ke daftar stok</a>

    <div class="flex flex-wrap justify-between gap-3 mt-5 mb-6">
        <div>
            <p class="text-sm text-slate-500">Detail inventaris</p>
            <h1 class="text-3xl font-bold">{{ $stock->name }}</h1>
            <p class="text-slate-500">{{ $stock->sku ?: 'Tanpa SKU' }}</p>
        </div>
        <span class="badge h-fit {{ $stock->status->badgeClass() }}">{{ $stock->status->label() }}</span>
    </div>

    <div class="grid lg:grid-cols-3 gap-5">
        <section class="panel p-5 lg:col-span-2" aria-label="Deskripsi barang">
            @if($stock->cover_image_path)
                <img src="/storage/{{ $stock->cover_image_path }}" alt="{{ $stock->name }}" class="w-full max-h-80 object-cover rounded-xl mb-5 border border-slate-200">
            @else
                <div class="h-48 rounded-xl bg-slate-100 grid place-items-center text-slate-600 mb-5">Belum ada cover image</div>
            @endif

            <div class="flex items-center justify-between mb-2">
                <h2 class="font-bold text-lg">Deskripsi barang (Markdown)</h2>
                @if(!$editing)
                    <button type="button" wire:click="$set('editing', true)" class="text-sm text-blue-700 font-semibold">Edit deskripsi</button>
                @endif
            </div>

            @if($editing)
                <div class="space-y-3">
                    <label for="detail-description" class="sr-only">Deskripsi barang (Markdown)</label>
                    <textarea id="detail-description" wire:model.live="description" rows="7" class="field font-mono text-sm" placeholder="Tulis deskripsi dengan format Markdown (**tebal**, *miring*, # Heading, - List)..." aria-label="Deskripsi barang (Markdown)" title="Deskripsi barang (Markdown)"></textarea>
                    @error('description')
                        <p class="text-red-600 text-xs">{{ $message }}</p>
                    @enderror
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                        <p class="text-xs font-semibold text-slate-600 uppercase mb-2">Preview Markdown</p>
                        <div class="prose max-w-none text-slate-700 text-sm">
                            {!! \Illuminate\Support\Str::markdown($description ?: '*Belum ada teks...*') !!}
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" wire:click="saveDescription" class="btn btn-primary">Simpan</button>
                        <button type="button" wire:click="$set('editing', false)" class="btn btn-muted">Batal</button>
                    </div>
                </div>
            @else
                <div class="prose max-w-none text-slate-600">
                    {!! \Illuminate\Support\Str::markdown($stock->description ?: 'Belum ada deskripsi.') !!}
                </div>
            @endif
        </section>

        <aside class="panel p-5 h-fit" aria-label="Informasi stok" title="Informasi stok">
            <h2 class="font-bold mb-4">Informasi stok</h2>
            <p class="text-sm text-slate-500">Jumlah tersedia</p>
            <div class="flex items-center gap-3 my-2">
                <button type="button" wire:click="adjust(-1)" class="btn btn-muted" aria-label="Kurangi stok">−</button>
                <b class="text-3xl">{{ $stock->quantity }}</b>
                <button type="button" wire:click="adjust(1)" class="btn btn-muted" aria-label="Tambah stok">＋</button>
                <span class="text-sm text-slate-500">{{ $stock->unit }}</span>
            </div>
            @error('quantity')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror

            <div class="border-t py-3 mt-4 space-y-2 text-sm">
                <p class="flex justify-between"><span class="text-slate-500">Minimum stok</span><b>{{ $stock->min_stock }} {{ $stock->unit }}</b></p>
                <p class="flex justify-between"><span class="text-slate-500">Harga satuan</span><b>{{ $stock->price !== null ? 'Rp '.number_format((float) $stock->price, 0, ',', '.') : '—' }}</b></p>
            </div>

            <form wire:submit="saveCover" class="border-t pt-4 mt-3">
                <label for="detail-cover" class="text-sm font-semibold">Cover image</label>
                <input id="detail-cover" type="file" wire:model="cover" accept="image/*" class="field mt-2" aria-label="Cover image" title="Cover image">
                <button type="submit" wire:loading.attr="disabled" wire:target="cover,saveCover" class="btn btn-primary w-full mt-3">
                    <span wire:loading.remove wire:target="cover,saveCover">Unggah cover</span>
                    <span wire:loading wire:target="cover,saveCover">Mengunggah...</span>
                </button>
                @error('cover')
                    <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                @enderror
            </form>
        </aside>
    </div>
</div>