<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityReservationDetail extends Model
{
    use HasFactory;

    protected $table = 'FacilityReservationDetail';
    protected $primaryKey = 'id_facility_reservation_detail';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_facility_reservation_detail',
        'reservation_id',
        'facility_id',
        'quantity',
        'price',
    ];

    /**
     * Get the reservation that owns the FacilityReservationDetail.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'id_reservation');
    }

    /**
     * Get the facility that owns the FacilityReservationDetail.
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id', 'id_facility');
    }
}