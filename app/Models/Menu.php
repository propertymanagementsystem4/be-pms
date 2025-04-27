<?php

namespace App\Models;

use App\Traits\LogActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory, HasUuids, LogActivity;

    protected $table = 'Menu';
    protected $primaryKey = 'id_menu';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_menu',
        'role_id',
        'name',
        'prefix',
        'path',
        'icon',
        'order',
    ];

    /**
     * Get the role that owns the Menu.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id_role');
    }

    /**
     * Get all the sub menus for the menu.
     */
    public function subMenus(): HasMany
    {
        return $this->hasMany(SubMenu::class, 'menu_id', 'id_menu');
    }
}