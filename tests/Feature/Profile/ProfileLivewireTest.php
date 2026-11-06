<?php

namespace Tests\Feature\Profile;

use App\Features\Profile\Livewire\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_profile_password_and_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'name' => 'Lama',
            'username' => 'user_lama',
            'email' => 'lama@example.com',
        ]);

        $this->actingAs($user)->get('/profile')->assertOk();

        $photo1 = UploadedFile::fake()->image('avatar1.jpg', 200, 200);

        Livewire::actingAs($user)
            ->test(Profile::class)
            ->set('name', 'Baru')
            ->set('username', 'user_baru')
            ->set('email', '   ')
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->set('photo', $photo1)
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertEquals('Baru', $user->name);
        $this->assertEquals('user_baru', $user->username);
        $this->assertNull($user->email);
        $this->assertTrue(Hash::check('newpassword123', $user->password));
        $this->assertNotNull($user->profile_photo_path);
        $oldPhoto = $user->profile_photo_path;
        Storage::disk('public')->assertExists($oldPhoto);

        // Replace existing photo without changing password
        $photo2 = UploadedFile::fake()->image('avatar2.jpg', 200, 200);
        Livewire::actingAs($user)
            ->test(Profile::class)
            ->set('photo', $photo2)
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }
}