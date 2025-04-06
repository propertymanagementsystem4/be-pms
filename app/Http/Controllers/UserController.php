<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use ApiResponse;

    public function getProfile()
    {
        try {
            $user = Auth::user();

            return $this->successResponse(200, [
                'id' => $user->id_user,
                'email' => $user->email,
                'fullname' => $user->fullname,
                'phone_number' => $user->phone_number,
                'image_url' => $user->image_url,
                'role' => $user->role->name,
            ], 'User retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve user');
        }
    }

    public function updateProfile(UserUpdateRequest $request)
    {
        try {
            $user = Auth::user();

            Log::info('User update request: ', $request->all());

            if ($request->hasFile('image_url')) {
                $imageController = new ImageController();
                $newImageUrl = $imageController->uploadProfilePicture($request->file('image_url'), $user->image_url);
                if ($newImageUrl) {
                    $user->image_url = $newImageUrl;
                }
            }

            $user->fullname = $request->input('fullname');
            $user->phone_number = $request->input('phone_number');
            $user->save();

            return $this->successResponse(200, $user, 'Profile updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update user profile: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to update user profile');
        }
    }
}
