<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'Facility';
    protected $primaryKey = 'id_facility';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_facility',
        'property_id',
        'name',
        'description',
        'price',
        'facility_code',
    ];

    /**
     * Get the property that owns the Facility.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'id_property');
    }

    /**
     * Get all the facility reservation details for the facility.
     */
    public function facilityReservationDetails(): HasMany
    {
        return $this->hasMany(FacilityReservationDetail::class, 'facility_id', 'id_facility');
    }
}