<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerData extends Model
{
    use HasFactory;

    protected $table = 'CustomerData';
    protected $primaryKey = 'id_customer_data';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_customer_data',
        'reservation_id',
        'fullname',
        'nik',
        'birth_date',
    ];

    /**
     * Get the reservation that owns the CustomerData.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'id_reservation');
    }
}