<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class eece_data_logging_site extends Model
{
    use HasFactory;

    protected $fillable = ['site_id', 'type', 'count', 'available', 'occupied'];
}
