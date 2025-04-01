<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'Room';
    protected $primaryKey = 'id_room';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_room',
        'property_id',
        'name',
        'description',
        'type',
        'price_per_night',
        'room_code',
    ];

    /**
     * Get the property that owns the Room.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'id_property');
    }

    // /**
    //  * Get all the room reservation details for the room.
    //  */
    // public function roomReservationDetails(): HasMany
    // {
    //     return $this->hasMany(RoomReservationDetail::class, 'room_id', 'id_room');
    // }

    // /**
    //  * Get all the images for the room.
    //  */
    // public function images(): HasMany
    // {
    //     return $this->hasMany(Image::class, 'room_id', 'id_room');
    // }
}