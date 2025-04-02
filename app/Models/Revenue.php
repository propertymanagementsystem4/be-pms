<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revenue extends Model
{
    use HasFactory;

    protected $table = 'Revenue';
    protected $primaryKey = 'id_revenue';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_revenue',
        'property_id',
        'month',
        'year',
        'total_revenue',
    ];

    /**
     * Get the property that owns the Revenue.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'id_property');
    }
}