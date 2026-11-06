<?php

namespace App\Features\Admin\Livewire;

use App\Features\Admin\Services\AdminUserService;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AdminUserStockList extends Component
{
    use WithPagination;

    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function render(AdminUserService $service)
    {
        return view('livewire.admin.admin-user-stock-list', [
            'stocks' => $service->stocksFor($this->user),
        ])->layout('components.layouts.app');
    }
}