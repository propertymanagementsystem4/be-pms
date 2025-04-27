<?php

namespace App\Models;

use App\Traits\LogActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubMenu extends Model
{
    use HasFactory, HasUuids, LogActivity;

    protected $table = 'SubMenu';
    protected $primaryKey = 'id_sub_menu';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_sub_menu',
        'menu_id',
        'name',
        'path',
        'order',
    ];

    /**
     * Get the menu that owns the SubMenu.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id_menu');
    }
}