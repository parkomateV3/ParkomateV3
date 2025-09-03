<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detection_type_site extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'type_id', 'name'];
}
