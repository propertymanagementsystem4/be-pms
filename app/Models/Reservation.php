<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'Reservation';
    protected $primaryKey = 'id_reservation';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_reservation',
        'property_id',
        'admin_id',
        'customer_id',
        'check_in_date',
        'check_out_date',
        'total_price',
        'payment_status',
        'reservation_status',
        'total_guest',
        'invoice_number',
    ];

    /**
     * Get the property that owns the Reservation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'id_property');
    }

    /**
     * Get the customer (user) who made the Reservation.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id', 'id_user');
    }

    /**
     * Get the admin (user) who handled the Reservation.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id', 'id_user');
    }

    /**
     * Get all the customer data associated with the reservation.
     */
    public function customerData(): HasOne
    {
        return $this->hasOne(CustomerData::class, 'reservation_id', 'id_reservation');
    }

    /**
     * Get all the facility reservation details for the reservation.
     */
    public function facilityReservationDetails(): HasMany
    {
        return $this->hasMany(FacilityReservationDetail::class, 'reservation_id', 'id_reservation');
    }

    /**
     * Get all the room reservation details for the reservation.
     */
    public function roomReservationDetails(): HasMany
    {
        return $this->hasMany(RoomReservationDetail::class, 'reservation_id', 'id_reservation');
    }

    /**
     * Get all the images associated with the reservation.
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'reservation_id', 'id_reservation');
    }
}