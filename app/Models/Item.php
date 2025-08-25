<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'content', 
        'status'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public const STATUS_ALLOWED = 'Allowed';
    public const STATUS_PROHIBITED = 'Prohibited';

    public static function getStatus(): array
    {
        return [
            self::STATUS_ALLOWED => 'Allowed',
            self::STATUS_PROHIBITED => 'Prohibited',
        ];
    }

    public function scopeAllowed($query)
    {
        return $query->where('status', self::STATUS_ALLOWED);
    }

    public function scopeProhibited($query)
    {
        return $query->where('status', self::STATUS_PROHIBITED);
    }
}
