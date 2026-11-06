<div>
    <a href="/admin/users" class="text-sm text-blue-700 font-medium">← Kembali ke pengguna</a>
    <p class="text-sm text-slate-500 mt-5">Inventaris member (Read-Only)</p>
    <h1 class="text-3xl font-bold">{{ $user->name }}</h1>
    <p class="text-slate-500 mb-6">@<span>{{ $user->username }}</span> · {{ $user->stocks()->count() }} barang</p>

    <div class="panel overflow-hidden">
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>Nama barang</th>
                        <th>SKU</th>
                        <th>Deskripsi</th>
                        <th>Quantity</th>
                        <th>Minimum</th>
                        <th>Harga</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        <tr wire:key="admin-stock-{{ $stock->id }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($stock->cover_image_path)
                                        <img src="/storage/{{ $stock->cover_image_path }}" alt="{{ $stock->name }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 grid place-items-center text-slate-600 text-xs font-bold shrink-0">IMG</div>
                                    @endif
                                    <span class="font-semibold">{{ $stock->name }}</span>
                                </div>
                            </td>
                            <td class="text-sm text-slate-500">{{ $stock->sku ?: '—' }}</td>
                            <td class="text-xs text-slate-600 max-w-xs">
                                <div class="prose prose-sm max-w-none">{!! \Illuminate\Support\Str::markdown($stock->description ?: '—') !!}</div>
                            </td>
                            <td>{{ $stock->quantity }} {{ $stock->unit }}</td>
                            <td>{{ $stock->min_stock }} {{ $stock->unit }}</td>
                            <td class="text-sm">{{ $stock->price !== null ? 'Rp '.number_format((float) $stock->price, 0, ',', '.') : '—' }}</td>
                            <td><span class="badge {{ $stock->status->badgeClass() }}">{{ $stock->status->label() }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-slate-500">Belum ada stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $stocks->links() }}</div>
    </div>
    <p class="text-xs text-slate-600 mt-3">Tampilan hanya baca — admin tidak dapat mengubah atau menghapus stok member.</p>
</div>