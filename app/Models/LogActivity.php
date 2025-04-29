<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogActivity extends Model
{
    use HasFactory;
    
    protected $table = 'LogActivity';
    protected $primaryKey = 'id_log_activity';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_log_activity',
        'user_id',
        'property_id',
        'module_name',
        'action',
        'old_data',
        'new_data',
        'date',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }
}