<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class eecs_device_info extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'device_id', 'device_name', 'detection_list'];
}
