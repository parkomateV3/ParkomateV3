<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessPoint extends Model
{
    use HasFactory;
    protected $fillable = ['admin_id', 'superadmin', 'site_id', 'view_data', 'add_data', 'edit_data', 'delete_data', 'status'];
}
