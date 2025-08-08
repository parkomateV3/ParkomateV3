<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class site_data_by_minute extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'data', 'date_time', 'data_analysis'];
}
