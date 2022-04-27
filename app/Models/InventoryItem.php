<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\InventoryParentItem;
use App\Models\Room;
use App\Models\TransferRequest;
use App\Models\RepairRequest;
use App\Models\BorrowRequest;

class InventoryItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    private $repairNotBrokenStatus = ['completed', 'rejected', 'disposed', 'replaced', 'PO created', 'repaired'];
    private $notTransferringStatus = ['completed', 'rejected', 'disposed'];
    private $notBorrowedStatus = ['returned', 'rejected'];

    public $fillable = [
        'brand',
        'serial_number',
        'inventory_parent_item_id',
        'room_id',
        'is_disposed',
        'borrow_request_id'
    ];

    protected $appends = ['repair_status', 'transfer_status', 'borrow_status'];

    public function getRepairStatusAttribute()
    {
        $id = $this->id;
        // return RepairRequest::where('item_id', $id)
        //     ->whereNotIn('status', $this->repairNotBrokenStatus)
        //     ->exists();

        $data = RepairRequest::where('item_id', $id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($data) {
            return $data->status;
        }

        return 'none';
    }

    public function getTransferStatusAttribute()
    {
        $id = $this->id;

        // return TransferRequest::where('item_id', $id)
        //     ->whereNotIn('status', $this->notTransferringStatus)
        //     ->exists();

        $data = TransferRequest::where('item_id', $id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($data) {
            return $data->status;
        }

        return 'none';
    }

    public function getBorrowStatusAttribute()
    {
        if ($this->borrow_request_id) {
            return 'borrowed';
        }
        return 'none';
    }

    public function components()
    {
        return $this->hasMany(PCComponentInstance::class, 'inventory_item_id');
    }

    public function inventory_parent_item()
    {
        return $this->belongsTo(InventoryParentItem::class)->withTrashed();
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
            ->whereNotIn('status', $this->notTransferringStatus)->select('id', 'item_id');
    }

    public function repair_requests()
    {
        return $this->hasMany(RepairRequest::class, 'item_id');
    }

    public function available_repair_requests()
    {
        return $this->hasMany(RepairRequest::class, 'item_id')
            ->whereNotIn('status', $this->repairNotBrokenStatus)->select('id', 'item_id');
    }
}
