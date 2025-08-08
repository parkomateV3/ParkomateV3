<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reservation_device_info extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'floor_id', 'zonal_id', 'reservation_number', 'reservation_name', 'status'];
}
