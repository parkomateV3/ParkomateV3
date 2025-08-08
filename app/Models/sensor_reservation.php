<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sensor_reservation extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'floor_id', 'zonal_id', 'sensor_id', 'barrier_unique_no', 'is_blocked', 'from_date_time', 'to_date_time', 'otp', 'unblocked_on'];
}
