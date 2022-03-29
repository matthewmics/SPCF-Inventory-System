<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PCComponent extends Model
{
    use HasFactory;

    protected $table = 'pc_components';

    protected $fillable = [
        'name'
    ];

    public function instances()
    {
        return $this->hasMany(PCComponentInstance::class, 'pc_component_id');
    }


    public function instances_id_only()
    {
        return $this->hasMany(PCComponentInstance::class, 'pc_component_id', 'id')->select('id', 'pc_component_id');
    }
}
