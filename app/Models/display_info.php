<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class display_info extends Model
{
    use HasFactory;
    use SoftDeletes;

    // protected $fillable = ['display_id', 'site_id', 'display_unique_no', 'floor_zonal_sensor_ids', 'floor_zonal_sensor_names', 'logic_to_calculate_no', 'display_format', 'location_of_the_display_on_site', 'intensity', 'panels', 'font_size', 'color'];
    protected $fillable = ['display_id', 'site_id', 'display_unique_no', 'intensity', 'panels', 'location_of_the_display_on_site'];
}
