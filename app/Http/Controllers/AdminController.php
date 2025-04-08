<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreAdminRequest;
use App\Models\PropertyManager;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    use ApiResponse;

    public function getAllAdmin()
    {
        try {
            $admins = User::where('role_id', 'adm')->orderBy('fullname', 'asc')->get();
            return $this->successResponse(200, $admins, 'Admin users retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve admins: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve admins');
        }
    }

    public function getAdminByPropertyId($propertyId)
    {
        try {
            if (!Str::isUuid($propertyId)) {
                return $this->badRequestResponse(400, 'Invalid property ID format');
            }

            $admins = PropertyManager::with('user')
                ->where('property_id', $propertyId)
                ->whereHas('user', function ($query) {
                    $query->where('role_id', 'adm');
                })
                ->get();

            if ($admins->isEmpty()) {
                return $this->notFoundResponse('No admin users found for this property');
            }

            return $this->successResponse(200, $admins, 'Admin users retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve admins by property: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to retrieve admins by property');
        }
    }

    public function storeAdmin(StoreAdminRequest $request)
    {
        try {
            $admin = User::create([
                'id_user' => Str::uuid(),
                'role_id' => 'adm',
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'fullname' => $request->fullname,
                'phone_number' => $request->phone_number,
                'is_verified' => true,
            ]);

            return $this->successResponse(201, $admin, 'Admin user created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create admin user: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to create admin user');
        }
    }

    public function destroyAdmin($id)
    {
        try {
            if (!Str::isUuid($id)) {
                return $this->badRequestResponse(400, 'Invalid admin ID format');
            }

            $admin = User::where('role_id', 'adm')->find($id);

            if (!$admin) {
                return $this->notFoundResponse('Admin user not found');
            }

            $admin->delete();
            return $this->successResponse(200, null, 'Admin user deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete admin user: ' . $e->getMessage());
            return $this->internalErrorResponse('Failed to delete admin user');
        }
    }
}
