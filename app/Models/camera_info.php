<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class camera_info extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'processor_id', 'local_ip_address', 'camera_access_link', 'image', 'camera_identifier', 'parking_slot_details'];
    
}
