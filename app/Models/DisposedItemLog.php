<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisposedItemLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'inventory_item_id';

    protected $fillable = [
        'inventory_item_id'
    ] ;
}
