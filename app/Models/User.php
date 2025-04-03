<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'User';
    protected $primaryKey = 'id_user';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_user',
        'role_id',
        'email',
        'password',
        'fullname',
        'phone_number',
        'is_verified',
        'image_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_verified' => 'boolean',
    ];

    /**
     * Get the role that owns the User.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id_role');
    }

    /**
     * Get all the property managers associated with the user.
     */
    public function propertyManagers(): HasMany
    {
        return $this->hasMany(PropertyManager::class, 'user_id', 'id_user');
    }

    /**
     * Get all the reservations where the user is the customer.
     */
    public function customerReservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'customer_id', 'id_user');
    }

    /**
     * Get all the reservations where the user is the admin.
     */
    public function adminReservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'admin_id', 'id_user');
    }

    // Override Laravel's default method to check if the user has verified their email
    public function hasVerifiedEmail()
    {
        return (bool) $this->is_verified;
    }

    public function markEmailAsVerified()
    {
        $this->is_verified = true;
        $this->save();
    }

    public function sendEmailVerificationNotification()
    {
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $this->id_user]
        );

        Log::info('Verification URL: ' . $verifyUrl);

        $this->notify(new VerifyEmailNotification($verifyUrl));
    }

}