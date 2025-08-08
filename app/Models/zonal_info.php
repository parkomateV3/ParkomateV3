<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class zonal_info extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['zonal_id', 'site_id', 'floor_id', 'zonal_unique_no', 'zonal_name'];
}
