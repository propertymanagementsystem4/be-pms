<?php

namespace App\Models;

use App\Traits\LogActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    use HasFactory, HasUuids, LogActivity;

    protected $table = 'Image';
    protected $primaryKey = 'id_image';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_image',
        'property_id',
        'room_id',
        'reservation_id',
        'img_url',
    ];

    /**
     * Get the property that owns the Image.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'id_property');
    }

    /**
     * Get the room that owns the Image.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'id_room');
    }

    /**
     * Get the reservation that owns the Image (if applicable).
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'id_reservation');
    }
}