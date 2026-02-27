<?php

namespace App\Models\Academy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubNavigation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'label',
        'slug',
        'new_tab',
        'min_rank',
        'order',
        'visible'
    ];

    protected $casts = [
        'visible' => 'boolean',
        'new_tab' => 'boolean',
        'min_rank' => 'integer',
    ];

    public function navigation()
    {
        return $this->belongsTo(Navigation::class);
    }

    protected static function booted()
    {
        static::saved(function () {
            \Cache::forget('navigations');
        });

        static::deleted(function () {
            \Cache::forget('navigations');
        });
    }
}
