<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomReservationDetail extends Model
{
    use HasFactory;

    protected $table = 'RoomReservationDetail';
    protected $primaryKey = 'id_room_reservation_detail';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_room_reservation_detail',
        'reservation_id',
        'room_id',
        'quantity',
        'price',
    ];

    /**
     * Get the reservation that owns the RoomReservationDetail.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'id_reservation');
    }

    /**
     * Get the room that owns the RoomReservationDetail.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'id_room');
    }
}