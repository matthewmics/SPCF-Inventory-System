<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\FileStorage;
use App\Models\User;
use App\Models\InventoryItem;

class RepairRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requestor_user_id',
        'handler_user_id',
        'file_storage_id',
        'item_id',
        'status',
        'item_type',
        'rejection_details',
        'details'
    ];
    
    public function file_storage()
    {
        return $this->belongsTo(FileStorage::class, 'file_storage_id')->withTrashed();
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
}
