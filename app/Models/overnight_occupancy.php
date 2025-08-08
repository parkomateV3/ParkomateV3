<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class overnight_occupancy extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'floor_id', 'data'];
}
