<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class symbol_on_display extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['ondisplay_id', 'display_id', 'symbol_to_show', 'coordinates', 'color'];
}