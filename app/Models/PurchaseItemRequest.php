<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
