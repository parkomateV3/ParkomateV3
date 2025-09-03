<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class processor_info extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'processor_id'];
}
