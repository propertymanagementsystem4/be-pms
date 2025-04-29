<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Type extends Model
{
    use HasFactory;

    protected $table = 'Type';
    protected $primaryKey = 'id_type';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_type',
        'property_id',
        'name',
        'price_per_night',
    ];

    /**
     * Get all the properties that belong to the type.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'id_property');
    }
    /**
     * Get all the rooms that belong to the type.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'type_id', 'id_type');
    }
}
