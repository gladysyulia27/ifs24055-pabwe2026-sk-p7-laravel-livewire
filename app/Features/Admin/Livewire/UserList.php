<?php
namespace App\Features\Admin\Livewire;
use Livewire\Component;use Livewire\WithPagination;use App\Features\Admin\Services\AdminUserService;
class UserList extends Component {use WithPagination;public string $search='';public function updatingSearch(){$this->resetPage();}public function render(AdminUserService $service){return view('livewire.admin.user-list',['users'=>$service->users($this->search)])->layout('components.layouts.app');}}
