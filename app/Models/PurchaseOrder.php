<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\JobOrder;
use App\Models\FileStorage;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $primaryKey = 'job_order_id';

    protected $fillable = [
        'file_storage_id',
        'job_order_id',
        'room_name',
        'item_name'
    ];

    public function job_order()
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function file_storage()
    {
        return $this->belongsTo(FileStorage::class);
    }
}
