<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class eecs_sensor_info extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'device_id', 'sensor_number', 'sensor_name', 'detection_type'];
}
