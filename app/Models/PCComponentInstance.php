<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PCComponentInstance extends Model
{
    use HasFactory;

    protected $table = 'pc_component_instances';

    protected $fillable = [
        'brand',
        'serial_number',
        'is_disposed',
        'pc_component_id',
        'inventory_item_id'
    ];

    public function component()
    {
        return $this->belongsTo(PCComponent::class, 'pc_component_id');
    }
}
