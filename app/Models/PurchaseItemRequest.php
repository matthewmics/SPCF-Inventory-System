<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PurchaseItemRequest extends Model
{
    use HasFactory;

    protected $table = 'purchase_item_requests';

    protected $fillable = [
        'requestor',
        'department',
        'to_purchase',
        'purpose',
        'item_type',
        'attached_file_id',
        'destination_room',

        'status',
        'rejection_details',

        'worker',
        'requested_by'
    ];

    public function worker_object()
    {
        return $this->belongsTo(User::class, 'worker')->withTrashed();
    }

    public function requestor_object()
    {
        return $this->belongsTo(User::class, 'requested_by')->withTrashed();
    }

    public function destination()
    {
        return $this->belongsTo(Room::class, 'destination_room')->withTrashed();
    }
}
