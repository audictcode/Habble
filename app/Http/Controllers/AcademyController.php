<?php

namespace App\Http\Controllers;

use App\Models\{
    Topic, Slide, Article, Badge, DailyMission,
    Topic\TopicCategory,
};
use App\Models\Article\ArticleCategory;
use App\Models\Academy\CampaignInfo;
use App\Models\Academy\CampaignInfoComment;
use App\Models\FurniValue;
use App\Models\WebGame;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AcademyController extends Controller
{
    public function submitCampaignComment(Request $request, CampaignInfo $campaign)
    {
        $data = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        if (!$campaign->active) {
            return back()->with('error', 'No puedes comentar una publicación inactiva.');
        }

        $campaign->comments()->create([
            'user_id' => (int) auth()->id(),
            'content' => trim($data['content']),
        ]);

        return back()->with('success', 'Comentario publicado correctamente.');
    }

    public function updateCampaignComment(Request $request, CampaignInfo $campaign, CampaignInfoComment $comment)
    {
        if ((int) $comment->campaign_info_id !== (int) $campaign->id) {
            abort(404);
        }

        if ((int) $comment->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $comment->update([
            'content' => trim($data['content']),
        ]);

        return back()->with('success', 'Comentario actualizado correctamente.');
    }

    public function destroyCampaignComment(CampaignInfo $campaign, CampaignInfoComment $comment)
    {
        if ((int) $comment->campaign_info_id !== (int) $campaign->id) {
            abort(404);
        }

        if ((int) $comment->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $comment->delete();

        return back()->with('success', 'Comentario eliminado correctamente.');
    }

    public function submitDjApplication(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120'],
            'habbo_user' => ['required', 'string', 'max:50'],
            'programs' => ['nullable', 'string', 'max:180'],
            'availability' => ['nullable', 'string', 'max:120'],
            'experience' => ['nullable', 'string', 'max:500'],
            'message' => ['required', 'string', 'max:1500'],
        ]);

        $subject = 'Solicitud DJ Habble - ' . $data['habbo_user'];
        $body = implode("\n", [
            'Nueva solicitud de DJ',
            'Nombre: ' . $data['name'],
            'Email: ' . $data['email'],
            'Habbo usuario: ' . $data['habbo_user'],
            'Programas para emitir: ' . ($data['programs'] ?? 'No especificados'),
            'Disponibilidad: ' . ($data['availability'] ?? 'No especificada'),
            '',
            'Experiencia:',
            trim((string) ($data['experience'] ?? 'No especificada')),
            '',
            'Mensaje:',
            trim((string) $data['message']),
        ]);

        try {
            Mail::raw($body, function ($mail) use ($subject, $data) {
                $mail->to('support@habble.org')
                    ->subject($subject)
                    ->replyTo($data['email'], $data['name']);
            });
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'No se pudo enviar la solicitud ahora. Inténtalo de nuevo en unos minutos.');
        }

        return back()->with('success', 'Solicitud enviada correctamente. El equipo de radio te contactará pronto.');
    }

    public function index()
    {
        $topics = Topic::getDefaultResources();
        $slides = Slide::getDefaultResources();
        $topicsCategories = TopicCategory::all();
        $hasHabboAssetsDate = Schema::hasColumn('badges', 'habboassets_source_created_at');
        $badgeReferralParams = [];

        $latestNewsCampaign = $this->campaignQueryByPage('noticias-campana')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first();

        if ($latestNewsCampaign) {
            $badgeReferralParams = $this->extractReferralParamsFromUrl(
                (string) ($latestNewsCampaign->primary_button_url ?: $latestNewsCampaign->secondary_button_url ?: '')
            );
        }

        $latestBadgesQuery = Badge::query()
            ->whereNotNull('code');

        if ($hasHabboAssetsDate) {
            $latestBadgesQuery
                ->orderByRaw("CASE WHEN habboassets_badge_id IS NULL THEN 1 ELSE 0 END ASC")
                ->orderByDesc('habboassets_badge_id')
                ->orderByRaw("CASE WHEN coalesce(habboassets_source_created_at, habbo_published_at) IS NULL THEN 1 ELSE 0 END ASC")
                ->orderByRaw("coalesce(habboassets_source_created_at, habbo_published_at) DESC")
                ->orderByDesc('id');
        } else {
            $latestBadgesQuery
                ->orderByRaw("CASE WHEN habbo_published_at IS NULL THEN 1 ELSE 0 END ASC")
                ->orderByDesc('habbo_published_at')
                ->orderByDesc('id');
        }

        $latestBadges = $latestBadgesQuery
            ->limit(12)
            ->get();

        return view('habboacademy.index',
            compact([
                'topics',
                'slides',
                'topicsCategories',
                'latestBadges',
                'badgeReferralParams',
            ])
        );
    }

    public function page(string $slug)
    {
        $normalizedSlug = Str::slug($slug);

        if (in_array($normalizedSlug, ['inicio', 'home', 'habble', 'habboacademy', 'habbo-academy'], true)) {
            return redirect()->route('web.academy.index');
        }

        if (in_array($normalizedSlug, ['habbo', 'placas-habbo', 'nuevas-placas'], true)) {
            return redirect()->route('web.pages.show', ['slug' => 'placas']);
        }

        if (in_array($normalizedSlug, ['fancenter', 'fan-center'], true)) {
            return redirect()->route('web.pages.show', ['slug' => 'generador-de-avatar']);
        }

        if (in_array($normalizedSlug, ['coleccionables', 'collectibles'], true)) {
            return redirect()->away('https://collectibles.habbo.com/');
        }

        if (in_array($normalizedSlug, ['generador-de-avatar', 'generador-avatar', 'extras'], true)) {
            $title = 'Generador de Avatar';

            return view('habboacademy.extras', compact('title', 'slug'));
        }

        if ($normalizedSlug === 'juegos') {
            $title = 'Panel de Juegos';
            $games = new Collection();
            if (Schema::hasTable('web_games')) {
                $gamesQuery = WebGame::query()->where('active', true);

                if (Schema::hasColumn('web_games', 'published_at')) {
                    $gamesQuery->where(function ($query) {
                        $query->whereNull('published_at')
                            ->orWhere('published_at', '<=', now());
                    })->orderByDesc('published_at');
                }

                if (Schema::hasColumn('web_games', 'participation_ends_at')) {
                    $gamesQuery->where(function ($query) {
                        $query->whereNull('participation_ends_at')
                            ->orWhere('participation_ends_at', '>=', now());
                    });
                }

                $games = $gamesQuery->latest()->get();
            }

            return view('habboacademy.games.index', compact('title', 'slug', 'games'));
        }

        if (in_array($normalizedSlug, ['misiones', 'misiones-diarias'], true)) {
            $title = 'Misiones Diarias';
            $slug = 'misiones-diarias';
            $missions = new Collection();
            if (Schema::hasTable('daily_missions')) {
                $missionsQuery = DailyMission::query()->where('active', true);

                if (Schema::hasColumn('daily_missions', 'published_at')) {
                    $missionsQuery->where(function ($query) {
                        $query->whereNull('published_at')
                            ->orWhere('published_at', '<=', now());
                    })->orderByDesc('published_at');
                }

                $missions = $missionsQuery->latest()->get();
            }

            $claimedToday = [];
            if (auth()->check() && Schema::hasTable('user_daily_mission_rewards')) {
                $claimedToday = DB::table('user_daily_mission_rewards')
                    ->where('user_id', auth()->id())
                    ->where('mission_date', now()->toDateString())
                    ->pluck('daily_mission_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }

            return view('habboacademy.missions.daily', compact('title', 'slug', 'missions', 'claimedToday'));
        }

        if (in_array($normalizedSlug, ['radio', 'se-locutor', 'locutor'], true)) {
            $title = $normalizedSlug === 'se-locutor' ? 'Sé locutor' : 'Radio';
            $slug = $normalizedSlug;

            return view('habboacademy.radio', compact('title', 'slug'));
        }

        if (in_array($normalizedSlug, ['horarios', 'horario'], true)) {
            $title = 'Horarios';
            $slug = 'horarios';

            return view('habboacademy.horarios', compact('title', 'slug'));
        }

        if (in_array($normalizedSlug, ['placas', 'todas-las-placas'], true)) {
            $title = 'Todas las placas';
            $category = request()->query('categoria', 'todos');
            $hasHabboAssetsDate = Schema::hasColumn('badges', 'habboassets_source_created_at');
            $badgeReferralParams = [];

            $latestNewsCampaign = $this->campaignQueryByPage('noticias-campana')
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->first();

            if ($latestNewsCampaign) {
                $badgeReferralParams = $this->extractReferralParamsFromUrl(
                    (string) ($latestNewsCampaign->primary_button_url ?: $latestNewsCampaign->secondary_button_url ?: '')
                );
            }

            $hotelCodes = ['ES', 'US', 'BR', 'DE', 'FR', 'IT', 'NL', 'FI', 'TR', 'PT'];
            $badgesQuery = Badge::query()
                ->whereNotNull('code');

            if ($category === 'hoteles') {
                $badgesQuery->where(function ($query) use ($hotelCodes) {
                    foreach ($hotelCodes as $code) {
                        $query->orWhereRaw('UPPER(code) LIKE ?', [strtoupper($code) . '%']);
                    }
                });
            } elseif ($category === 'juegos') {
                $badgesQuery->where(function ($query) {
                    $query->orWhereRaw('UPPER(code) LIKE ?', ['GAM%'])
                        ->orWhereRaw('UPPER(code) LIKE ?', ['GAME%'])
                        ->orWhereRaw('UPPER(code) LIKE ?', ['JUEGO%'])
                        ->orWhereRaw('UPPER(code) LIKE ?', ['WOB%'])
                        ->orWhereRaw('UPPER(code) LIKE ?', ['BB%']);
                });
            } elseif ($category === 'fansites') {
                $badgesQuery->where(function ($query) {
                    $query->orWhereRaw('UPPER(code) LIKE ?', ['FS%'])
                        ->orWhereRaw('UPPER(code) LIKE ?', ['FAN%'])
                        ->orWhereRaw('UPPER(code) LIKE ?', ['FSC%']);
                });
            } elseif ($category === 'eventos') {
                $badgesQuery->where(function ($query) {
                    $query->where('rarity', 'event')
                        ->orWhereRaw('UPPER(code) LIKE ?', ['EV%'])
                        ->orWhereRaw('UPPER(code) LIKE ?', ['XMAS%'])
                        ->orWhereRaw('UPPER(code) LIKE ?', ['HWEEN%']);
                });
            }

            if ($hasHabboAssetsDate) {
                $badgesQuery
                    ->orderByRaw("CASE WHEN habboassets_badge_id IS NULL THEN 1 ELSE 0 END ASC")
                    ->orderByDesc('habboassets_badge_id')
                    ->orderByRaw("CASE WHEN coalesce(habboassets_source_created_at, habbo_published_at) IS NULL THEN 1 ELSE 0 END ASC")
                    ->orderByRaw("coalesce(habboassets_source_created_at, habbo_published_at) DESC")
                    ->orderByDesc('id');
            } else {
                $badgesQuery
                    ->orderByRaw("CASE WHEN habbo_published_at IS NULL THEN 1 ELSE 0 END ASC")
                    ->orderByDesc('habbo_published_at')
                    ->orderByDesc('id');
            }

            $badges = $badgesQuery
                ->paginate(120)
                ->withQueryString();

            return view('habboacademy.badges.catalog', compact('title', 'slug', 'badges', 'category', 'badgeReferralParams'));
        }

        if (in_array($normalizedSlug, [
            'todos-los-furnis',
            'toda-la-ropa',
            'todos-los-rares',
            'todos-los-sonidos',
            'todos-los-animales',
            'todos-los-efectos',
        ], true)) {
            $catalogMap = [
                'todos-los-furnis' => ['title' => 'Todos los Furnis', 'category' => null],
                'todos-los-rares' => ['title' => 'Todos los Rares', 'category' => 'Rares'],
                'toda-la-ropa' => ['title' => 'Toda la Ropa', 'category' => 'Ropa'],
                'todos-los-animales' => ['title' => 'Todos los Animales', 'category' => 'Animales'],
                'todos-los-efectos' => ['title' => 'Todos los Efectos', 'category' => 'Efectos'],
                'todos-los-sonidos' => ['title' => 'Todos los Sonidos', 'category' => 'Sonidos'],
            ];

            $title = $catalogMap[$normalizedSlug]['title'];
            $targetCategory = $catalogMap[$normalizedSlug]['category'];
            $slug = $normalizedSlug;
            $query = trim((string) request()->query('query', ''));

            $furnisQuery = FurniValue::query()
                ->with('category')
                ->where(function ($query) {
                    $query->whereNotNull('image_path')
                        ->orWhereNotNull('icon_path');
                })
                ->orderByDesc('updated_at')
                ->orderByDesc('id');

            if ($targetCategory !== null) {
                $furnisQuery->whereHas('category', function ($query) use ($targetCategory) {
                    $query->whereRaw('lower(name) = ?', [strtolower($targetCategory)]);
                });
            }

            if ($query !== '') {
                $furnisQuery->where(function ($builder) use ($query) {
                    $builder->where('name', 'like', '%' . $query . '%')
                        ->orWhere('habboassets_hotel', 'like', '%' . $query . '%')
                        ->orWhere('source_provider', 'like', '%' . $query . '%')
                        ->orWhere('price_type', 'like', '%' . $query . '%')
                        ->orWhere('state', 'like', '%' . $query . '%')
                        ->orWhereRaw('cast(habboassets_furni_id as text) like ?', ['%' . $query . '%']);
                });
            }

            $furnis = $furnisQuery->simplePaginate(48)->withQueryString();

            return view('habboacademy.furni.catalog', compact('title', 'slug', 'furnis', 'targetCategory', 'query'));
        }

        if (in_array($normalizedSlug, ['verificacion-placas', 'verificar-placas'], true)) {
            $title = 'Verificación de placas';
            $slug = 'verificacion-placas';

            return view('habboacademy.badges.verification', compact('title', 'slug'));
        }

        if (in_array($normalizedSlug, ['noticias', 'novedades', 'contenidos', 'contents', 'todas-las-noticias'], true)) {
            $title = 'Todas las noticias';

            $articlesQuery = Article::query()
                ->with(['user', 'category'])
                ->whereReviewed(true)
                ->whereStatus(true)
                ->orderByDesc('fixed');

            if (Schema::hasColumn('articles', 'published_at')) {
                $articlesQuery->where(function ($query) {
                    $query->whereNull('published_at')
                        ->orWhere('published_at', '<=', now());
                })->orderByDesc('published_at');
            }

            $articles = $articlesQuery
                ->orderByDesc('id')
                ->get();

            $hasCampaignCategoryColumn = Schema::hasColumn('campaign_infos', 'category_id');
            $newsCampaignCategoryIds = $this->campaignCategoryIdsForPage('noticias-campana');
            $infoCampaignCategoryIds = $this->campaignCategoryIdsForPage('informacion-campana');
            $campaignCategoryIds = array_values(array_unique(array_merge($newsCampaignCategoryIds, $infoCampaignCategoryIds)));

            $campaignNews = $this->campaignBaseQuery()
                ->where(function ($query) use ($campaignCategoryIds, $hasCampaignCategoryColumn) {
                    $query->whereIn('target_page', ['noticias-campana', 'informacion-campana']);
                    if ($hasCampaignCategoryColumn && !empty($campaignCategoryIds)) {
                        $query->orWhereIn('category_id', $campaignCategoryIds);
                    }
                })
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->get();

            $newsItems = $this->paginateMergedNews($articles, $campaignNews, 20);
            $slug = 'todas-las-noticias';

            return view('habboacademy.news.index', compact('title', 'slug', 'newsItems'));
        }

        if (in_array($normalizedSlug, ['noticias-campana', 'noticias-campaña'], true)) {
            $title = 'Noticias campaña';
            $campaignNewsQuery = $this->campaignQueryByPage('noticias-campana')
                ->orderByDesc('published_at')
                ->orderByDesc('id');

            $selectedNews = null;
            $campaignComments = null;
            if (request()->filled('entry')) {
                $selectedNews = (clone $campaignNewsQuery)->where('id', (int) request()->query('entry'))->first();
                if ($selectedNews) {
                    $campaignComments = $selectedNews->comments()
                        ->with('user')
                        ->latest()
                        ->paginate(12, ['*'], 'comments_page');
                }
            }

            $campaignNews = $selectedNews ? collect([$selectedNews]) : $campaignNewsQuery->paginate(10);

            $slug = 'noticias-campana';

            return view('habboacademy.campaign.news', compact('title', 'slug', 'campaignNews', 'selectedNews', 'campaignComments'));
        }

        if (in_array($normalizedSlug, ['informacion-campana', 'informacion-campaña'], true)) {
            return $this->campaignInfoPage();
        }

        if (in_array($normalizedSlug, ['foro', 'temas'], true)) {
            $title = 'Foro';
            $topicsBaseQuery = Topic::query()
                ->with('user')
                ->orderByDesc('fixed')
                ->orderByDesc('id');

            $topics = (clone $topicsBaseQuery)
                ->whereStatus(true)
                ->paginate(10);

            if ($topics->count() === 0 && (clone $topicsBaseQuery)->count() > 0) {
                $topics = $topicsBaseQuery->paginate(10);
            }

            $slug = $normalizedSlug;

            return view('habboacademy.forum.index', compact('title', 'slug', 'topics'));
        }

        $title = ucfirst(str_replace('-', ' ', $slug));

        return view('habboacademy.page', compact('title', 'slug'));
    }

    public function campaignInfoPage(?string $campaignSlug = null)
    {
        $slug = 'informacion-campana';
        $campaignInfos = $this->campaignQueryByPage('informacion-campana')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        $campaignInfo = null;
        if ($campaignInfos->isNotEmpty()) {
            if (!filled($campaignSlug)) {
                $latest = $campaignInfos->first();
                return redirect(url('/pages/informacion-campana/' . $this->campaignInfoPublicSlug($latest)));
            }

            $normalizedRequestedSlug = Str::slug((string) $campaignSlug);
            $campaignInfo = $campaignInfos->first(function (CampaignInfo $item) use ($normalizedRequestedSlug) {
                return $this->campaignInfoPublicSlug($item) === $normalizedRequestedSlug;
            });

            if (!$campaignInfo) {
                abort(404);
            }
        }

        $campaignComments = null;
        if ($campaignInfo) {
            $campaignComments = $campaignInfo->comments()
                ->with('user')
                ->latest()
                ->paginate(12, ['*'], 'comments_page');
        }

        $title = $campaignInfo?->title ?: 'Información campaña';

        return view('habboacademy.campaign.info', compact('title', 'slug', 'campaignInfo', 'campaignComments'));
    }

    private function campaignBaseQuery()
    {
        return CampaignInfo::query()
            ->with('category')
            ->where('active', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    private function campaignQueryByPage(string $page)
    {
        $normalizedPage = $this->normalizeCampaignCategoryName($page);
        $hasCampaignCategoryColumn = Schema::hasColumn('campaign_infos', 'category_id');
        $categoryIds = $this->campaignCategoryIdsForPage($normalizedPage);

        return $this->campaignBaseQuery()
            ->where(function ($query) use ($normalizedPage, $categoryIds, $hasCampaignCategoryColumn) {
                $query->where('target_page', $normalizedPage);

                if ($hasCampaignCategoryColumn && !empty($categoryIds)) {
                    $query->orWhereIn('category_id', $categoryIds);
                }
            });
    }

    private function campaignCategoryIdsForPage(string $page): array
    {
        if (!Schema::hasTable('articles_categories')) {
            return [];
        }

        $normalizedPage = $this->normalizeCampaignCategoryName($page);

        return ArticleCategory::query()
            ->get(['id', 'name'])
            ->filter(function (ArticleCategory $category) use ($normalizedPage) {
                return $this->normalizeCampaignCategoryName((string) $category->name) === $normalizedPage;
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function normalizeCampaignCategoryName(string $value): string
    {
        $slug = Str::slug($value);

        if (in_array($slug, ['noticias-campana', 'noticia-campana', 'noticias-de-campana'], true)) {
            return 'noticias-campana';
        }

        if (in_array($slug, ['informacion-campana', 'informacion-campana-mensual', 'info-campana'], true)) {
            return 'informacion-campana';
        }

        return $slug;
    }

    private function paginateMergedNews(Collection $articles, Collection $campaignNews, int $perPage = 20): LengthAwarePaginator
    {
        $merged = $articles
            ->concat($campaignNews)
            ->sortByDesc(function ($item) {
                $date = $item->published_at ?? $item->created_at ?? null;
                $timestamp = 0;

                if ($date instanceof \DateTimeInterface) {
                    $timestamp = $date->getTimestamp();
                } elseif ($date) {
                    $timestamp = strtotime((string) $date) ?: 0;
                }

                return sprintf('%012d%012d', $timestamp, (int) ($item->id ?? 0));
            })
            ->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pageItems = $merged->forPage($currentPage, $perPage)->values();

        return new LengthAwarePaginator(
            $pageItems,
            $merged->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    private function extractReferralParamsFromUrl(string $url): array
    {
        $url = trim($url);
        if ($url === '') {
            return [];
        }

        $queryString = parse_url($url, PHP_URL_QUERY);
        if (!is_string($queryString) || $queryString === '') {
            return [];
        }

        parse_str($queryString, $params);
        if (!is_array($params)) {
            return [];
        }

        $allowedKeys = [
            'ref',
            'referral',
            'referral_code',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_id',
        ];

        $filtered = [];
        foreach ($allowedKeys as $key) {
            if (isset($params[$key]) && $params[$key] !== '') {
                $filtered[$key] = (string) $params[$key];
            }
        }

        return $filtered;
    }

    private function campaignInfoPublicSlug(CampaignInfo $campaignInfo): string
    {
        $preferred = trim((string) ($campaignInfo->slug ?: $campaignInfo->month_label ?: $campaignInfo->title));
        $normalized = Str::slug($preferred);

        return $normalized !== '' ? $normalized : ('campana-' . (int) $campaignInfo->id);
    }
}
