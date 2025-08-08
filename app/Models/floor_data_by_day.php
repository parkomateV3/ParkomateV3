<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class floor_data_by_day extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'floor_id', 'date', 'check_in_count', 'check_out_count', 'max_count', 'min_count', 'min_time', 'max_time', 'avg_time', 'expected_amount'];
}
