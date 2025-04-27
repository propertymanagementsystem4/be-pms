<?php

namespace App\Traits;

use App\Models\LogActivity as ModelsLogActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait LogActivity
{
    public static function bootLogActivity()
    {
        static::saved(function ($model) {
            if ($model->wasRecentlyCreated) {
                static::storeLog($model, static::class, 'CREATED');
            } else {
                if (!$model->wasChanged()) {
                    return;
                }
                static::storeLog($model, static::class, 'UPDATED');
            }
        });

        static::deleted(function ($model) {
            static::storeLog($model, static::class, 'DELETED');
        });
    }

    public static function getTagName(Model $model)
    {
        return !empty($model->tag_name) ? $model->tag_name : Str::title(Str::snake(class_basename($model), ' '));
    }

    public static function activeUserId()
    {
        $guard = static::activeUserGuard();
        $user = Auth::guard($guard)->user();
    
        return $user ? ($user->id_user ?? null) : null;
    }

    public static function activeUserGuard()
    {
        foreach (array_keys(config('auth.guards')) as $guard) {
            if (auth()->guard($guard)->check()) {
                return $guard;
            }
        }
        return null;
    }

    public static function storeLog($model, string $modelPath, string $action)
    {
        try {
            $newDatas = null;
            $oldDatas = null;
    
            if ($action === 'CREATED') {
                $newDatas = $model->getAttributes();
            } elseif ($action === 'UPDATED') {
                $oldDatas = $model->getOriginal();
                $newDatas = $model->getChanges();
            } elseif ($action === 'DELETED') {
                $oldDatas = $model->getOriginal();
            }
    
            $logActivity = new ModelsLogActivity();
            $logActivity->id_log_activity = Str::uuid();
            $logActivity->system_logable_id = $model->getKey();
            $logActivity->system_logable_type = $modelPath;
            $logActivity->user_id = static::activeUserId();
            $logActivity->guard_name = static::activeUserGuard();
            $logActivity->module_name = static::getTagName($model);
            $logActivity->action = $action;
            $logActivity->old_data = !empty($oldDatas) ? json_encode($oldDatas) : null;
            $logActivity->new_data = !empty($newDatas) ? json_encode($newDatas) : null;
            $logActivity->ip_address = request()->ip();
            $logActivity->date = now();
            $logActivity->save();
        } catch (\Exception $e) {
            Log::error('LogActivity save failed: ' . $e->getMessage());
        }
    }
}
