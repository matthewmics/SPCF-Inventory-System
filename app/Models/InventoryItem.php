<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\InventoryParentItem;
use App\Models\Room;
use App\Models\TransferRequest;
use App\Models\RepairRequest;

class InventoryItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $fillable = [
        'brand',
        'serial_number',
        'inventory_parent_item_id',
        'room_id',
        'is_disposed'
    ];

    public function components()
    {
        return $this->hasMany(PCComponentInstance::class, 'inventory_item_id');
    }

    public function inventory_parent_item()
    {
        return $this->belongsTo(InventoryParentItem::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function transfer_requests()
    {
        return $this->hasMany(TransferRequest::class, 'item_id');
    }

    public function available_transfer_requests()
    {
        return $this->hasMany(TransferRequest::class, 'item_id')
            ->whereNotIn('status', ['completed', 'rejected', 'disposed'])->select('id', 'item_id');
    }

    public function repair_requests()
    {
        return $this->hasMany(RepairRequest::class, 'item_id');
    }

    public function available_repair_requests()
    {
        return $this->hasMany(RepairRequest::class, 'item_id')
            ->whereNotIn('status', ['completed', 'rejected', 'disposed', 'replaced', 'PO created', 'repaired'])->select('id', 'item_id');
    }
}
