<?php

namespace Tests\Unit\Services;

use App\Features\Profile\Services\ProfileService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_profile_data_password_and_photo(): void
    {
        Storage::fake('public');
        $service = new ProfileService();
        $user = User::factory()->create();

        $photo1 = UploadedFile::fake()->image('p1.jpg');
        $service->update($user, [
            'name' => 'Updated User',
            'username' => 'updated_user',
            'email' => '',
            'password' => 'newpass123',
        ], $photo1);

        $user->refresh();
        $this->assertSame('Updated User', $user->name);
        $this->assertNull($user->email);
        $this->assertTrue(Hash::check('newpass123', $user->password));
        $firstPath = $user->profile_photo_path;
        Storage::disk('public')->assertExists($firstPath);

        $photo2 = UploadedFile::fake()->image('p2.jpg');
        $service->update($user, [
            'name' => 'Updated User 2',
            'username' => 'updated_user',
            'password' => '',
        ], $photo2);

        $user->refresh();
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }
}