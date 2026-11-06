<?php

namespace App\Features\Admin\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminUserService
{
    public function users(string $search = ''): LengthAwarePaginator
    {
        return User::withCount('stocks')
            ->where('role', 'member')
            ->when($search, fn ($q) => $q->where(fn ($x) => $x
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(12);
    }

    public function stocksFor(User $user): LengthAwarePaginator
    {
        return $user->stocks()->latest()->paginate(12);
    }
}