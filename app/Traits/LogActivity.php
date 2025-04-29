<?php

namespace App\Traits;

use App\Models\LogActivity as ModelsLogActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait LogActivity
{
    public function logActivity($propertyId = null, string $module, string $action, array $newData = null, array $oldData = null)
    {
        ModelsLogActivity::create([
            'id_log_activity' => Str::uuid(),
            'user_id' => Auth::id(),
            'property_id' => $propertyId,
            'module_name' => $module,
            'action' => $action,
            'new_data' => $newData,
            'old_data' => $oldData,
            'date' => now(),
        ]);
    }
}
