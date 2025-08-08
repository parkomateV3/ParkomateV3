<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class email_log extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'subject', 'content', 'status', 'sensor_id', 'device'];
}
