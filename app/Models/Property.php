<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'Property';
    protected $primaryKey = 'id_property';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_property',
        'name',
        'description',
        'location',
        'total_rooms',
        'property_code',
        'city',
        'province',
    ];

    /**
     * Get all the facilities for the property.
     */
    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class, 'property_id', 'id_property');
    }

    /**
     * Get all the property managers for the property.
     */
    public function propertyManagers(): HasMany
    {
        return $this->hasMany(PropertyManager::class, 'property_id', 'id_property');
    }

    /**
     * Get all the reservations for the property.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'property_id', 'id_property');
    }

    /**
     * Get all the revenues for the property.
     */
    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class, 'property_id', 'id_property');
    }

    /**
     * Get all the types for the property.
     */
    public function types(): HasMany
    {
        return $this->hasMany(Type::class, 'property_id', 'id_property');
    }

    /**
     * Get all the images for the property.
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'property_id', 'id_property');
    }
}