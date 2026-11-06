<div class="max-w-3xl">
    <p class="text-sm text-slate-500">Pengaturan akun</p>
    <h1 class="text-3xl font-bold mt-1 mb-6">Profil Saya</h1>

    <form wire:submit="save" class="panel p-6 space-y-4">
        <div class="flex items-center gap-4 pb-4 border-b border-slate-100">
            @if(auth()->user()->profile_photo_path)
                <img src="/storage/{{ auth()->user()->profile_photo_path }}" alt="{{ auth()->user()->name }}" class="w-16 h-16 rounded-full object-cover border border-slate-200">
            @else
                <div class="w-16 h-16 rounded-full bg-blue-600 text-white grid place-items-center text-xl font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <p class="font-bold text-lg">{{ auth()->user()->name }}</p>
                <p class="text-sm text-slate-500">@<span>{{ auth()->user()->username }}</span> · {{ ucfirst(auth()->user()->role) }}</p>
            </div>
        </div>

        <div>
            <label for="profile-name" class="text-sm font-semibold">Nama lengkap</label>
            <input id="profile-name" wire:model="name" class="field mt-1" placeholder="Nama lengkap" aria-label="Nama lengkap" title="Nama lengkap">
            @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="profile-username" class="text-sm font-semibold">Username</label>
            <input id="profile-username" wire:model="username" class="field mt-1" placeholder="Username" aria-label="Username" title="Username">
            @error('username')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="profile-email" class="text-sm font-semibold">Email</label>
            <input id="profile-email" type="email" wire:model="email" class="field mt-1" placeholder="Email" aria-label="Email" title="Email">
            @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="profile-photo" class="text-sm font-semibold">Foto profil</label>
            <input id="profile-photo" type="file" wire:model="photo" class="field mt-1" accept="image/*" aria-label="Foto profil" title="Foto profil">
            @error('photo')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="border-t pt-4">
            <h2 class="font-bold">Ubah password</h2>
            <p class="text-xs text-slate-500">Kosongkan jika tidak ingin mengubah password.</p>
            <label for="profile-password" class="sr-only">Password baru</label>
            <input id="profile-password" type="password" wire:model="password" placeholder="Password baru" aria-label="Password baru" title="Password baru" class="field mt-3">
            @error('password')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            <label for="profile-password-confirmation" class="sr-only">Konfirmasi password</label>
            <input id="profile-password-confirmation" type="password" wire:model="password_confirmation" placeholder="Konfirmasi password" aria-label="Konfirmasi password" title="Konfirmasi password" class="field mt-2">
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:target="save,photo" class="btn btn-primary">Simpan perubahan</button>
    </form>
</div>