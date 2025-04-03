<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use ApiResponse;
    
    public function register(RegisterRequest $request)
    {
        try {
            // Check if the email already exists
            if (User::where('email', $request->email)->exists()) {
                return $this->errorResponse('Email already exists', 422);
            }

            $role = Role::where('id_role', 'cust')->firstOrFail()->id_role;

            $user = User::create([
                'id_user' => Str::uuid(),
                'role_id' => $role,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'fullname' => $request->fullname,
                'phone_number' => $request->phone_number,
                'is_verified' => false,
            ]);

            $user->sendEmailVerificationNotification();

            $responseData = [
                'email' => $user->email,
                'fullname' => $user->fullname,
                'phone_number' => $user->phone_number,
                'role' => $user->role->name,
            ];

            return $this->successResponse(201, $responseData, 'Registration successful. Please check your email to verify your account.');
        } catch (\Exception $e) {
            Log::error('Registration Failed: ' . $e->getMessage());
            return $this->internalErrorResponse('Registration failed');
        }
    }

    public function verifyEmail(HttpRequest $request, $id)
    {
        try {
            if (!$request->hasValidSignature()) {
                return $this->badRequestResponse(400, 'Invalid or expired link.');
            }
        
            $user = User::findOrFail($id);
        
            if ($user->is_verified) {
                return $this->badRequestResponse(400, 'Email already verified.');
            }
        
            $user->markEmailAsVerified();

            return $this->successResponse(200, null, 'Email verified successfully.');
        } catch (\Exception $e) {
            Log::error('Email Verification Error: ' . $e->getMessage());
            return $this->internalErrorResponse('Email verification failed');
        }
    }

    public function resendVerificationEmail(HttpRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->badRequestResponse(404, 'User not found.');
            }

            if ($user->is_verified) {
                return $this->badRequestResponse(400, 'Email already verified.');
            }

            $user->sendEmailVerificationNotification();

            return $this->successResponse(200, null, 'Verification email resent successfully.');
        } catch (\Exception $e) {
            Log::error('Resend Verification Email Error: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to resend verification email');
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            // Find user by email
            $user = User::where('email', $request->email)->first();

            // Check if the user exists
            if (!$user) {
                return $this->badRequestResponse(401, 'User not found.');
            }

            // Check if the password is correct
            if (!Hash::check($request->password, $user->password)) {
                return $this->badRequestResponse(401, 'Invalid password.');
            }

            // Check if the account is verified
            if (!$user->is_verified) {
                return $this->badRequestResponse(403, 'Your account is not verified. Please check your email.');
            }

            $token = $user->createToken('authToken')->plainTextToken;

            $responseData = [
                'token' => $token,
                'user' => [
                    'email' => $user->email,
                    'fullname' => $user->fullname,
                    'phone_number' => $user->phone_number,
                    'image' => $user->image_url,
                    'role' => $user->role->name,
                ],
            ];

            return $this->successResponse(200, $responseData, 'Login successful.');
        } catch (\Exception $e) {
            Log::error('Login Error: ' . $e->getMessage());
            return $this->internalErrorResponse('Login failed');
        }
    }

    public function logout()
    {
        try {
            $user = Auth::user();

            if ($user) {
                $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
            }

            return $this->successResponse(200, null, 'Logout successful.');
        } catch (\Exception $e) {
            Log::error('Logout Error: ' . $e->getMessage());
            return $this->internalErrorResponse('Logout failed');
        }
    }
}
