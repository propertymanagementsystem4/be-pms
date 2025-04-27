<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    use HasFactory;
    
    protected $table = 'LogActivity';
    protected $primaryKey = 'id_log_activity';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'system_logable_id',
        'system_logable_type',
        'user_id',
        'guest_name',
        'module_name',
        'action',
        'old_data',
        'new_data',
        'ip_address',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];
}
