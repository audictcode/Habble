<?php

namespace App\Models\Academy;

use App\Services\AcademyService;
use Throwable;
use Illuminate\Support\Collection;
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

        $cached = \Cache::get(self::NAVIGATION_CACHE_KEY);
        if ($cached instanceof Collection && $cached->isNotEmpty()) {
            return $cached;
        }

        try {
            $navigation = Navigation::defaultQuery();

            if ($subNavigations) {
                $navigation->with(['subNavigations' => function ($query) {
                    return $query->whereVisible(true);
                }]);
            }

            $result = $navigation->get();

            if ($result->isNotEmpty() && $cacheableTime > 0) {
                \Cache::put(self::NAVIGATION_CACHE_KEY, $result, $cacheableTime);
            }

            if ($result->isNotEmpty()) {
                return $result;
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return self::fallbackNavigation($subNavigations);
    }

    private static function fallbackNavigation(bool $subNavigations): Collection
    {
        $items = [
            [
                'label' => 'Inicio',
                'small_icon' => 'fas fa-house-user',
                'hover_icon' => asset('/images/menu/inicio.png'),
                'slug' => '/home',
                'order' => 0,
                'visible' => true,
                'sub' => [],
            ],
            [
                'label' => 'Habble',
                'small_icon' => 'fab fa-hackerrank',
                'hover_icon' => asset('/images/menu/habble.png'),
                'slug' => '/pages/noticias',
                'order' => 1,
                'visible' => true,
                'sub' => [
                    ['label' => 'Sobre nosotros', 'slug' => '/pages/habble'],
                    ['label' => 'Equipo', 'slug' => '/pages/equipo'],
                ],
            ],
            [
                'label' => 'Habbo',
                'small_icon' => 'fab fa-hire-a-helper',
                'hover_icon' => asset('/images/menu/habbo.png'),
                'slug' => '/pages/placas',
                'order' => 2,
                'visible' => true,
                'sub' => [
                    ['label' => 'Nuevas placas', 'slug' => '/pages/placas'],
                    ['label' => 'Verificación placas', 'slug' => '/pages/verificacion-placas'],
                ],
            ],
            [
                'label' => 'Contenidos',
                'small_icon' => 'fab fa-neos',
                'hover_icon' => asset('/images/menu/contenidos.png'),
                'slug' => '/pages/noticias',
                'order' => 3,
                'visible' => true,
                'sub' => [
                    ['label' => 'Todas las noticias', 'slug' => '/pages/noticias'],
                    ['label' => 'Foro', 'slug' => '/pages/foro'],
                    ['label' => 'Noticias campaña', 'slug' => '/pages/noticias-campana'],
                    ['label' => 'Informacion campaña', 'slug' => '/pages/informacion-campana'],
                ],
            ],
            [
                'label' => 'Fan Center',
                'small_icon' => 'fas fa-magic',
                'hover_icon' => asset('/images/menu/fancenter.png'),
                'slug' => '/pages/generador-de-avatar',
                'order' => 4,
                'visible' => true,
                'sub' => [
                    ['label' => 'Generador de avatar', 'slug' => '/pages/generador-de-avatar'],
                ],
            ],
            [
                'label' => 'Radio',
                'small_icon' => 'fas fa-music',
                'hover_icon' => asset('/images/menu/radio.png'),
                'slug' => '/pages/radio',
                'order' => 5,
                'visible' => true,
                'sub' => [
                    ['label' => 'Horarios', 'slug' => '/pages/horarios'],
                    ['label' => 'Sé locutor', 'slug' => '/pages/se-locutor'],
                ],
            ],
        ];

        return collect($items)->map(function (array $item) use ($subNavigations) {
            $navigation = new self([
                'label' => $item['label'],
                'small_icon' => $item['small_icon'],
                'hover_icon' => $item['hover_icon'],
                'slug' => $item['slug'],
                'order' => $item['order'],
                'visible' => $item['visible'],
            ]);

            if ($subNavigations) {
                $subNavs = collect($item['sub'])->map(function (array $sub, int $index) {
                    return new SubNavigation([
                        'label' => $sub['label'],
                        'slug' => $sub['slug'],
                        'new_tab' => $sub['new_tab'] ?? false,
                        'min_rank' => $sub['min_rank'] ?? null,
                        'order' => $sub['order'] ?? ($index + 1),
                        'visible' => true,
                    ]);
                });

                $navigation->setRelation('subNavigations', $subNavs);
            }

            return $navigation;
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
