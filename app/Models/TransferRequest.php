<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\InventoryItem;
use App\Models\Room;
use App\Models\User;
use App\Models\FileStorage;

class TransferRequest extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'handler_user_id',
        'file_storage_id',
        'requestor_user_id',
        'current_room_id',
        'destination_room_id',
        'item_id',
        'rejection_details',
        'details',
        'item_type',
        'status'
    ];

    public function file_storage()
    {
        return $this->belongsTo(FileStorage::class, 'file_storage_id');
    }

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

    public function notes()
    {
        return $this->hasMany(Note::class, 'transfer_id');
    }
}
