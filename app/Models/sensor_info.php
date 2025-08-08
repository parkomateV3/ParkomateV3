<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class sensor_info extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['sensor_id', 'site_id', 'floor_id', 'zonal_id', 'sensor_unique_no', 'sensor_name', 'sensor_range', 'color_occupied', 'color_available', 'role', 'barrier_id', 'barrier_color', 'near_piller'];
}
