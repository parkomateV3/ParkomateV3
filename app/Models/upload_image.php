<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class upload_image extends Model
{
    use HasFactory;

    protected $fillable = ['filename', 'filepath', 'sensor'];
}
