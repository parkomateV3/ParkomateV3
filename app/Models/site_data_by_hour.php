<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class site_data_by_hour extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'date_time_slot', 'check_in_count', 'check_out_count', 'max_count', 'min_count', 'min_time', 'max_time', 'avg_time'];
}
