<div>
    <p class="text-sm text-slate-500">Administrasi</p>
    <h1 class="text-3xl font-bold mt-1 mb-6">Pengguna Member</h1>

    <div class="panel p-5">
        <label for="admin-user-search" class="sr-only">Cari nama atau username</label>
        <input id="admin-user-search" wire:model.live.debounce.300ms="search" class="field mb-4" placeholder="Cari nama atau username..." aria-label="Cari nama atau username" title="Cari nama atau username">
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Total barang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr wire:key="admin-user-{{ $user->id }}">
                            <td class="font-semibold">{{ $user->name }}</td>
                            <td>@<span>{{ $user->username }}</span></td>
                            <td class="text-sm text-slate-500">{{ $user->email ?: '—' }}</td>
                            <td><span class="badge badge-green">{{ $user->stocks_count }} barang</span></td>
                            <td>
                                <a href="/admin/users/{{ $user->id }}/stocks" class="text-blue-700 font-semibold text-sm">Lihat stok →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-slate-500">Pengguna tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $users->links() }}</div>
    </div>
    <p class="text-xs text-slate-600 mt-3">Mode admin bersifat read-only. Data stok member tidak dapat diubah dari halaman ini.</p>
</div>