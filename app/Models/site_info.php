<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class site_info extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['site_id', 'site_name', 'site_username', 'site_city', 'site_state', 'site_country', 'site_location', 'site_status', 'site_type_of_product', 'number_of_floors', 'number_of_zonals', 'number_of_sensors', 'number_of_displays', 'email', 'report_frequency', 'site_logo', 'ad_image', 'api_key', 'overtime_hours'];
}
