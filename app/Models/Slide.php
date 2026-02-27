<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;

class Slide extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'slug', 'active',
        'image_path', 'published_at', 'fixed', 'new_tab'
    ];

    protected $casts = [
        'active' => 'boolean',
        'fixed' => 'boolean',
        'new_tab' => 'boolean',
        'published_at' => 'datetime',
    ];

    public static function getDefaultResources()
    {
        $query = Slide::query();
        $hasPublishedAt = Schema::hasColumn('slides', 'published_at');

        if ($hasPublishedAt) {
            $query->where(function ($builder) {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
        }

        $activeSlidesQuery = (clone $query)
            ->whereActive(true);

        if ($hasPublishedAt) {
            $activeSlidesQuery->orderByDesc('published_at');
        }

        $activeSlides = $activeSlidesQuery
            ->orderByDesc('fixed')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        if ($activeSlides->isNotEmpty()) {
            return $activeSlides;
        }

        if ($hasPublishedAt) {
            $query->orderByDesc('published_at');
        }

        return $query
            ->orderByDesc('fixed')
            ->orderByDesc('id')
            ->limit(10)
            ->get();
    }
}
