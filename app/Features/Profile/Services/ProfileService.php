<?php

namespace App\Features\Profile\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function update(User $user, array $data, $photo = null): void
    {
        unset($data['photo'], $data['password_confirmation']);

        if (array_key_exists('email', $data) && trim((string) $data['email']) === '') {
            $data['email'] = null;
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($photo) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $data['profile_photo_path'] = $photo->store('profile-photos', 'public');
        }

        $user->update($data);
    }
}
