<?php

namespace Tests\Feature\Auth;

use App\Features\Auth\Livewire\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_member_can_login_with_username_or_email(): void
    {
        $member = User::factory()->create([
            'username' => 'budi_member',
            'email' => 'budi@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'member',
        ]);

        Livewire::test(Login::class)
            ->set('username', 'budi_member')
            ->set('password', 'secret123')
            ->call('login')
            ->assertRedirect('/stocks');

        auth()->logout();

        Livewire::test(Login::class)
            ->set('username', 'budi@example.com')
            ->set('password', 'secret123')
            ->call('login')
            ->assertRedirect('/stocks');

        $this->assertAuthenticatedAs($member);
    }

    public function test_admin_redirects_to_admin_users_on_login(): void
    {
        User::factory()->create([
            'username' => 'superadmin',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);

        Livewire::test(Login::class)
            ->set('username', 'superadmin')
            ->set('password', 'secret123')
            ->call('login')
            ->assertRedirect('/admin/users');
    }

    public function test_invalid_credentials_return_validation_error(): void
    {
        User::factory()->create([
            'username' => 'budi_member',
            'password' => Hash::make('secret123'),
        ]);

        Livewire::test(Login::class)
            ->set('username', 'budi_member')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['username']);
    }
}