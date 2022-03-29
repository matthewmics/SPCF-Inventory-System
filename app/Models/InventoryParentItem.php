<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\InventoryItem;

class InventoryParentItem extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $fillable = [
        'name', 'item_type'
    ];

    public function inventory_items()
    {
        return $this->hasMany(InventoryItem::class);
    }
}
