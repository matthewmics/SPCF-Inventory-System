<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Room;
use App\Models\InventoryItem;

class BorrowRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'handler_user_id',
        'requestor_user_id',
        'current_room_id',
        'destination_room_id',
        'item_id',
        'rejection_details',
        'details',
        'item_type',
        'status'
    ];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id')->withTrashed();
    }

    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_user_id')->withTrashed();
    }
    
    public function handler()
    {
        return $this->belongsTo(User::class, 'handler_user_id')->withTrashed();
    }

    public function current_room()
    {
        return $this->belongsTo(Room::class, 'current_room_id')->withTrashed();
    }

    public function destination_room()
    {
        return $this->belongsTo(Room::class, 'destination_room_id')->withTrashed();
    }
}
