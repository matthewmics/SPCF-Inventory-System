<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentBuilding extends Model
{

    use HasFactory;

    protected $table = 'department_building';

    protected $fillable = ['user_id', 'building_id'];
}
