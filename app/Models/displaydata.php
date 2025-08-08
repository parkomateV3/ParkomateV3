<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class displaydata extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['data_id', 'display_id', 'site_id', 'coordinates', 'floor_zonal_sensor_ids', 'floor_zonal_sensor_names', 'logic_calculate_number', 'display_format', 'math', 'font_size', 'font', 'color'];
}
