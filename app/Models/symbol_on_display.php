<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class symbol_on_display extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'symbol_on_displays';
    protected $primaryKey = 'ondisplay_id';

    protected $fillable = ['ondisplay_id', 'display_id', 'symbol_to_show', 'coordinates', 'color'];
}