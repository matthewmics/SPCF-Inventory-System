<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferRequestHistory extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'transfer_request_id',
        'handler_user_id'
    ];
}
