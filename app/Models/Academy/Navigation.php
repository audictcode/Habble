<?php

namespace App\Models\Academy;

use App\Services\AcademyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Navigation extends Model
{
    use HasFactory;

    protected const NAVIGATION_CACHE_KEY = 'navigations';

    public $timestamps = false;

    protected $fillable = [
        'label',
        'hover_icon',
        'slug',
        'order',
        'visible'
    ];

    protected $casts = [
        'visible' => 'boolean'
    ];

    public static function defaultQuery()
    {
        return Navigation::whereVisible(true)
            ->orderBy('order')
            ->orderByDesc('id');
    }

    public function subNavigations()
    {
        return $this->hasMany(SubNavigation::class)
            ->orderBy('order')
            ->orderByDesc('id');
    }

    public static function getAcademyNavigation(bool $subNavigations = true)
    {
        $cacheableTime = AcademyService::isDevEnvironment() ? 0 : 300;

        return \Cache::remember(self::NAVIGATION_CACHE_KEY, $cacheableTime, function() use ($subNavigations) {
            $navigation = Navigation::defaultQuery();

            if($subNavigations) {
                $navigation->with(['subNavigations' => function($query) {
                    return $query->whereVisible(true);
                }]);
            }

            return $navigation->get();
        });
    }

    protected static function booted()
    {
        static::saved(function () {
            \Cache::forget(self::NAVIGATION_CACHE_KEY);
        });

        static::deleted(function () {
            \Cache::forget(self::NAVIGATION_CACHE_KEY);
        });
    }
}
