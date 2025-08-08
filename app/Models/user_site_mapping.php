<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_site_mapping extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'site_id', 'can_edit'];
}
