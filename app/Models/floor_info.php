<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class floor_info extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['floor_id', 'site_id', 'floor_name', 'floor_image', 'piller_name', 'piller_coordinates', 'current_location_symbol', 'destination_location_symbol', 'interconnect_location_symbol', 'symbol_size', 'floor_image_sensor_mapping', 'floor_image_sensor_mapping_dimenssion', 'car_scale', 'floor_map_coordinate', 'label_properties', 'max_count', 'measured_count'];

    protected $primaryKey = 'floor_id';
}
