<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class symbol_info extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['symbol_id', 'symbol_name', 'symbol_img', 'binary_data', 'symbol_size'];
}