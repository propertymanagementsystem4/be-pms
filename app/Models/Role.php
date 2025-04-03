<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $table = 'Role';
    protected $primaryKey = 'id_role';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_role',
        'name',
    ];

    /**
     * Get all the users that belong to the role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id', 'id_role');
    }

    /**
     * Get all the menus that belong to the role.
     */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class, 'role_id', 'id_role');
    }
}