<?php
namespace App\Features\Profile\Services;
use App\Models\User;use Illuminate\Support\Facades\Hash;use Illuminate\Support\Facades\Storage;
class ProfileService {public function update(User $user,array $data, $photo=null):void{if(!empty($data['password']))$data['password']=Hash::make($data['password']);else unset($data['password']);if($photo){if($user->profile_photo_path)Storage::disk('public')->delete($user->profile_photo_path);$data['profile_photo_path']=$photo->store('profile-photos','public');}$user->update($data);}}
