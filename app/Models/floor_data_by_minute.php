<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class floor_data_by_minute extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'floor_id', 'data', 'date_time', 'data_analysis'];
}
