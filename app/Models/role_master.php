<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class role_master extends Model
{
    use HasFactory;

    protected $fillable = ['role_name'];
}
