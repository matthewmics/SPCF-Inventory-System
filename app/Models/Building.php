<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Room;
use App\Models\User;

class Building extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $fillable = [
        "name"
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function departments()
    {
        return $this->belongsToMany(User::class, 'department_building', 'department_id', 'user_id');
    }
}
