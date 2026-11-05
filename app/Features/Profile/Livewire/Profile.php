<?php

namespace App\Features\Profile\Livewire;

use App\Features\Profile\Services\ProfileService;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public $photo = null;

    public function mount(): void
    {
        $u = auth()->user();
        $this->name = $u->name;
        $this->username = $u->username;
        $this->email = (string) ($u->email ?? '');
    }

    public function save(ProfileService $service): void
    {
        $u = auth()->user();
        if (trim($this->email) === '') {
            $this->email = '';
        }

        $data = $this->validate([
            'name' => 'required|string|max:120',
            'username' => 'required|string|max:40|alpha_dash|unique:users,username,'.$u->id,
            'email' => 'nullable|email|max:150|unique:users,email,'.$u->id,
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|max:3072',
        ]);

        $service->update($u, $data, $this->photo);
        $this->reset(['password', 'password_confirmation', 'photo']);
        session()->flash('success', 'Profil berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.profile.profile')->layout('components.layouts.app');
    }
}
