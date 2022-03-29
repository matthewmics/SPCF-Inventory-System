<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Building;
use App\Models\InventoryItem;

class Room extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $fillable = [
        "name",
        "room_type",
        "building_id"
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function inventory_items()
    {
        return $this->hasMany(InventoryItem::class)->where('is_disposed', false);
    }
}
