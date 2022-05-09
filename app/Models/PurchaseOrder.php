<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\JobOrder;
use App\Models\FileStorage;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_storage_id',
        'job_order_id',
        'purchase_item_request_id',
        'room_name',
        'item_name',
        'is_completed'
    ];

    public function job_order()
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function file_storage()
    {
        return $this->belongsTo(FileStorage::class);
    }

    public function purchase_item_request()
    {
        return $this->belongsTo(PurchaseItemRequest::class, 'purchase_item_request_id');
    }
}
