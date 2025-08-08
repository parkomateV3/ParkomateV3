<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class floor_interconnection extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['floor_interconnection_id', 'site_id', 'floor_info'];
}
