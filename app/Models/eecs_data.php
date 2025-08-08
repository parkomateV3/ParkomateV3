<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class eecs_data extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'device_id', 'sensor_id', 'type', 'from', 'to'];
}
