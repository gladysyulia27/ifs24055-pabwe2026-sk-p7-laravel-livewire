<?php

namespace App\Features\Auth\Livewire;

use App\Features\Auth\Actions\LoginUserAction;
use Livewire\Component;

class Login extends Component
{
    public string $username = '';
    public string $password = '';
    public bool $remember = false;

    public function login(LoginUserAction $action)
    {
        $this->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $action->execute($this->username, $this->password, $this->remember);

        return redirect()->intended(auth()->user()->role === 'admin' ? '/admin/users' : '/stocks');
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.guest');
    }
}