<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;

class InventoryParentItem extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $fillable = [
        'name', 'item_type'
    ];

    protected $appends = ['qty', 'qty_available'];

    public function getQtyAttribute()
    {

        $id = $this->id;
        return DB::table('inventory_items')->where('inventory_parent_item_id', $id)
            ->where('is_disposed', false)
            ->count();
    }

    public function getQtyAvailableAttribute()
    {
        $id = $this->id;
        return DB::table('inventory_items')->where('inventory_parent_item_id', $id)
            ->whereNull('room_id')
            ->where('is_disposed', false)
            ->count();
    }

    public function inventory_items()
    {
        return $this->hasMany(InventoryItem::class);
    }
}
