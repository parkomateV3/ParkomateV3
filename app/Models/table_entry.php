<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class table_entry extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['entry_id', 'table_id', 'site_id', 'entry_name', 'floor_zonal_sensor_ids', 'floor_zonal_sensor_names', 'logic_to_calculate_numbers'];
}
