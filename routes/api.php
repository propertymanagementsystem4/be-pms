<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::get('/email/verify/{id}', 'verifyEmail')
        ->name('verification.verify');
    Route::post('/email/resend', 'resendVerificationEmail');
    Route::post('/login', 'login');
});

// ROUTE ALL ROLE
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('/profile')->controller(UserController::class)->group(function () {
        Route::get('/', 'getProfile');
        Route::put('/update/{id}', 'updateProfile');
    });
    
});

// ROUTE OWNER AND ADMIN
Route::middleware(['auth:sanctum', 'role:OWNER|ADMIN'])->group(function () {
    Route::prefix('/type')->controller(TypeController::class)->group(function () {
        Route::get('/list/{propertyId}', 'getTypeByPropertyId');
        Route::post('/create', 'storeType');
        Route::get('/detail/{id}', 'getDetailType');
        Route::put('/update/{id}', 'updateType');
        Route::delete('/delete/{id}', 'destroyType');
        Route::get('/search/{propertyId}', 'searchType');
    });

    Route::prefix('/room')->controller(RoomController::class)->group(function () {
        Route::get('/list/{propertyId}', 'getRoomByPropertyId');
        Route::post('/create', 'storeRoom');
        Route::get('/detail/{id}', 'getDetailRoom');
        Route::put('/update/{id}', 'updateRoom');
        Route::delete('/delete/{id}', 'destroyRoom');
        Route::get('/search/{propertyId}', 'searchRoom');
    });

    Route::prefix('/facility')->controller(FacilityController::class)->group(function () {
        Route::get('/list', 'getAllFacility');
        Route::get('/list/{propertyId}', 'getFacilityByPropertyId');
        Route::post('/create', 'storeFacility');
        Route::get('/detail/{id}', 'getDetailFacility');
        Route::put('/update/{id}', 'updateFacility');
        Route::delete('/delete/{id}', 'destroyFacility');
        Route::get('/search/{propertyId}', 'searchFacility');
    });
});

// ROUTE OWNER ONLY
Route::middleware(['auth:sanctum', 'role:OWNER'])->group(function () {
    Route::prefix('/property-owner')->controller(PropertyController::class)->group(function () {
        Route::get('/list', 'getAllProperty');
        Route::post('/create', 'storeProperty');
        Route::get('/detail/{id}', 'getDetailProperty');
        Route::put('/update/{id}', 'updateProperty');
        Route::delete('/delete', 'destroyProperty');
        Route::get('/search', 'searchProperty');
        Route::post('/assign-admin', 'assignAdminToProperty');
        Route::delete('/remove-admin', 'deleteAdminFromProperty');
    });

    Route::prefix('/admin')->controller(AdminController::class)->group(function () {
        Route::get('/list', 'getAllAdmin');
        Route::get('/list/{propertyId}', 'getAdminByPropertyId');
        Route::post('/create', 'storeAdmin');
        Route::delete('/delete/{id}', 'destroyAdmin');
    });

    Route::prefix('/menu')->controller(MenuController::class)->group(function () {
        Route::get('/list', 'getAllMenu');
        Route::get('/list-by-role', 'getMenuByRoleId');
        Route::post('/create', 'storeMenu');
        Route::get('/detail/{id}', 'getMenuById');
        Route::put('/update/{id}', 'updateMenu');
        Route::delete('/delete/{id}', 'destroyMenu');
    });

    Route::prefix('/menu/submenu')->controller(MenuController::class)->group(function () {
        Route::post('/create', 'storeSubmenu');
        Route::put('/update/{id}', 'updateSubmenu');
        Route::delete('/delete/{id}', 'destroySubmenu');
    });
    
});

// ROUTE ADMIN ONLY
Route::middleware(['auth:sanctum', 'role:ADMIN'])->group(function () {
    Route::prefix('/property-admin')->controller(PropertyController::class)->group(function () {
        Route::get('/list/{adminId}', 'getAllPropertyManageByAdmin');
        Route::get('/detail/{adminId}/{propertyId}', 'getDetailPropertyManageByAdmin');
        Route::get('/detail/by-code/{adminId}/{code}', 'getDetailPropertyByCodeManagedByAdmin');
        Route::put('/update/{adminId}/{propertyId}', 'updatePropertyManageByAdmin');
        Route::get('/search/{adminId}', 'searchPropertyManagedByAdmin');
    });

});

// ROUTE CUSTOMER ONLY
Route::middleware(['auth:sanctum', 'role:CUSTOMER'])->group(function () {
    
});

