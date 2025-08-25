<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleSheetSetting extends Model
{
    protected $fillable = [
        'url', 
        'sheet'
    ];
}
