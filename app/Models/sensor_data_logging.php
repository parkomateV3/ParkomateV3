<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class sensor_data_logging extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['sensor', 'status', 'site_id', 'floor_id', 'zonal_id', 'date_time', 'color', 'number', 'car_color', 'near_piller', 'car_image'];
}
