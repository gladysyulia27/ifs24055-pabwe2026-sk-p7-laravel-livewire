<?php

namespace Tests\Feature\Auth;

use App\Features\Auth\Livewire\Register;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_can_be_rendered(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_new_user_can_register_as_member(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Siti Aminah')
            ->set('username', 'siti_aminah')
            ->set('email', 'siti@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertRedirect('/stocks');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'siti_aminah',
            'email' => 'siti@example.com',
            'role' => 'member',
        ]);
    }
}