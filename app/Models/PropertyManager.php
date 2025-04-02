<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyManager extends Model
{
    use HasFactory;

    protected $table = 'PropertyManager';
    protected $primaryKey = 'id_property_manager';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_property_manager',
        'user_id',
        'property_id',
    ];

    /**
     * Get the user that owns the PropertyManager.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    /**
     * Get the property that owns the PropertyManager.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'id_property');
    }
}