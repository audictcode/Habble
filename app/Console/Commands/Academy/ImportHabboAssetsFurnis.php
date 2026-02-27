<?php

namespace App\Console\Commands\Academy;

use App\Models\Furni\FurniCategory;
use App\Models\FurniValue;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportHabboAssetsFurnis extends Command
{
    private const DEFAULT_BASE_URL = 'https://www.habboassets.com/furniture?hotel=__HOTEL__&page=__PAGE__';
    private const FALLBACK_BASE_URLS = [
        'https://www.habboassets.com/furniture?hotel=__HOTEL__&page=__PAGE__',
        'https://www.habboassets.com/c/furniture?hotel=__HOTEL__&page=__PAGE__',
        'https://habboassets.com/furniture?hotel=__HOTEL__&page=__PAGE__',
        'https://habboassets.com/c/furniture?hotel=__HOTEL__&page=__PAGE__',
    ];

    protected $signature = 'furnis:import-habboassets
        {--base-url= : URL plantilla de origen (usa __HOTEL__ y __PAGE__)}
        {--hotels=es,com,com.br,de,fr,it,nl,fi,tr : Hoteles separados por coma}
        {--max-pages=0 : Máximo de páginas por hotel (0 = sin límite)}
        {--category=auto : Categoría destino (auto para clasificar por tipo)}
        {--dry-run : Simular sin guardar en base de datos}';

    protected $description = 'Importa furnis desde HabboAssets (HTML/JSON) en múltiples hoteles';

    public function handle(): int
    {
        $baseUrl = trim((string) $this->option('base-url'));
        if ($baseUrl === '') {
            $baseUrl = self::DEFAULT_BASE_URL;
        }
        $maxPages = max(0, (int) $this->option('max-pages'));
        $categoryName = trim((string) $this->option('category'));
        $dryRun = (bool) $this->option('dry-run');

        $hotels = collect(explode(',', (string) $this->option('hotels')))
            ->map(fn ($hotel) => trim((string) $hotel))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($hotels)) {
            $this->error('No hay hoteles válidos.');
            return self::FAILURE;
        }

        $adminId = User::query()
            ->where('disabled', false)
            ->where('rank', '>=', 7)
            ->orderByDesc('rank')
            ->value('id') ?? User::query()->orderBy('id')->value('id');

        if (!$adminId) {
            $this->error('No existe usuario para asignar admin_id en furni_values.');
            return self::FAILURE;
        }

        $categoryCache = [];
        $manualCategory = null;
        if (strtolower($categoryName) !== 'auto') {
            $manualCategory = FurniCategory::query()->firstOrCreate(
                ['name' => $categoryName],
                ['icon' => 'fa-couch']
            );
        }

        $processed = 0;
        $created = 0;
        $updated = 0;

        foreach ($hotels as $hotel) {
            $this->line("Hotel: {$hotel}");
            $page = 1;
            $seenPageFingerprints = [];

            while (true) {
                if ($maxPages > 0 && $page > $maxPages) {
                    break;
                }

                $candidateUrls = $this->buildCandidateUrls($baseUrl, $hotel, $page);
                $url = $candidateUrls[0];
                $response = null;
                $lastError = null;

                foreach ($candidateUrls as $candidateUrl) {
                    $url = $candidateUrl;
                    try {
                        $response = Http::timeout(45)->get($candidateUrl);
                    } catch (\Throwable $exception) {
                        $lastError = $exception->getMessage();
                        continue;
                    }

                    if ($response->ok()) {
                        break;
                    }

                    $lastError = 'HTTP ' . $response->status();
                    $response = null;
                }

                if (!$response) {
                    $this->warn("  Página {$page}: error de red/HTTP ({$lastError})");
                    break;
                }

                if ($response->ok() && $url !== $baseUrl) {
                    $baseUrl = $url;
                }

                $items = $this->extractItems((string) $response->body());
                if (count($items) === 0) {
                    $this->line("  Página {$page}: sin items detectados (fin de páginas)");
                    break;
                }

                $this->line("  Página {$page}: " . count($items) . " items detectados");
                $pageIdentity = array_values(array_map(static function (array $item): string {
                    $sourceId = $item['id'] ?? $item['furni_id'] ?? $item['item_id'] ?? '';
                    $name = $item['name'] ?? $item['title'] ?? $item['classname'] ?? $item['furniName'] ?? '';
                    $image = $item['image'] ?? $item['image_url'] ?? $item['thumbnail'] ?? $item['icon'] ?? $item['iconUrl'] ?? '';

                    return trim((string) $sourceId) . '|' . trim((string) $name) . '|' . trim((string) $image);
                }, $items));
                $fingerprint = md5(json_encode($pageIdentity));

                if (isset($seenPageFingerprints[$fingerprint])) {
                    $this->line("  Página {$page}: resultados repetidos detectados (fin de páginas)");
                    break;
                }
                $seenPageFingerprints[$fingerprint] = true;

                foreach ($items as $item) {
                    $normalized = $this->normalizeItem($item, $hotel, $url);
                    if (!$normalized) {
                        continue;
                    }

                    $processed++;

                    $categoryId = $manualCategory?->id ?? $this->resolveCategoryId($item, $normalized['name'], $categoryCache);

                    $query = FurniValue::query()->where('habboassets_hotel', $hotel);
                    if (!empty($normalized['habboassets_furni_id'])) {
                        $query->where('habboassets_furni_id', $normalized['habboassets_furni_id']);
                    } else {
                        $query->where('name', $normalized['name']);
                    }

                    $existing = $query->first();

                    if ($existing) {
                        if (!$dryRun) {
                            $existing->fill($normalized)->save();
                        }
                        $updated++;
                    } else {
                        if (!$dryRun) {
                            FurniValue::query()->create($normalized + [
                                'category_id' => $categoryId,
                                'admin_id' => $adminId,
                                'price' => null,
                                'price_type' => 'coins',
                                'state' => 'regular',
                            ]);
                        }
                        $created++;
                    }
                }

                $page++;
            }
        }

        $mode = $dryRun ? 'SIMULACIÓN' : 'IMPORTACIÓN';
        $this->info("{$mode} furnis finalizada. Procesados: {$processed}, creados: {$created}, actualizados: {$updated}");

        return self::SUCCESS;
    }

    private function extractItems(string $body): array
    {
        $items = [];

        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            $items = array_merge($items, $this->extractFromArray($decoded));
        }

        if (preg_match('/<script[^>]*id="__NEXT_DATA__"[^>]*>(.*?)<\/script>/si', $body, $matches)) {
            $nextJson = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $nextDecoded = json_decode($nextJson, true);
            if (is_array($nextDecoded)) {
                $items = array_merge($items, $this->extractFromArray($nextDecoded));
            }
        }

        return collect($items)
            ->filter(fn ($item) => is_array($item))
            ->values()
            ->all();
    }

    private function extractFromArray(array $node): array
    {
        $items = [];

        if ($this->looksLikeFurniNode($node)) {
            $items[] = $node;
        }

        foreach ($node as $value) {
            if (is_array($value)) {
                $items = array_merge($items, $this->extractFromArray($value));
            }
        }

        return $items;
    }

    private function looksLikeFurniNode(array $node): bool
    {
        $hasName = isset($node['name']) || isset($node['title']) || isset($node['classname']) || isset($node['furniName']);
        $hasImage = isset($node['image']) || isset($node['image_url']) || isset($node['icon']) || isset($node['iconUrl']) || isset($node['thumbnail']);

        return $hasName && $hasImage;
    }

    private function normalizeItem(array $item, string $hotel, string $sourceUrl): ?array
    {
        $name = trim((string) ($item['name'] ?? $item['title'] ?? $item['classname'] ?? $item['furniName'] ?? ''));
        if ($name === '') {
            return null;
        }

        $image = (string) ($item['image'] ?? $item['image_url'] ?? $item['thumbnail'] ?? $item['icon'] ?? $item['iconUrl'] ?? '');
        if ($image === '') {
            return null;
        }

        if (Str::startsWith($image, '//')) {
            $image = 'https:' . $image;
        } elseif (!Str::startsWith($image, ['http://', 'https://'])) {
            $image = 'https://www.habboassets.com/' . ltrim($image, '/');
        }

        $sourceId = $item['id'] ?? $item['furni_id'] ?? $item['item_id'] ?? null;
        $sourceId = is_numeric($sourceId) ? (int) $sourceId : null;

        return [
            'name' => $name,
            'image_path' => $image,
            'icon_path' => $image,
            'habboassets_furni_id' => $sourceId,
            'habboassets_hotel' => $hotel,
            'habboassets_source_url' => $sourceUrl,
            'source_provider' => 'habboassets',
            'imported_from_habboassets_at' => now(),
            'external_metadata' => [
                'provider' => 'habboassets',
                'source_url' => $sourceUrl,
                'raw_hotel' => $hotel,
                'raw_item' => $item,
            ],
        ];
    }

    private function resolveCategoryId(array $item, string $name, array &$cache): int
    {
        $haystack = strtolower($name . ' ' . json_encode($item));

        $categoryName = 'Furnis normales';

        if ($this->containsAny($haystack, ['rare', 'raro', 'ltd', 'limited'])) {
            $categoryName = 'Rares';
        } elseif ($this->containsAny($haystack, ['clothing', 'ropa', 'shirt', 'hair', 'hat', 'shoe', 'outfit'])) {
            $categoryName = 'Ropa';
        } elseif ($this->containsAny($haystack, ['pet', 'animal', 'mascota'])) {
            $categoryName = 'Animales';
        } elseif ($this->containsAny($haystack, ['effect', 'efecto', 'fx'])) {
            $categoryName = 'Efectos';
        } elseif ($this->containsAny($haystack, ['sound', 'music', 'audio', 'jukebox', 'sonido'])) {
            $categoryName = 'Sonidos';
        }

        if (!isset($cache[$categoryName])) {
            $icons = [
                'Rares' => 'fa-gem',
                'Furnis normales' => 'fa-couch',
                'Ropa' => 'fa-shirt',
                'Animales' => 'fa-paw',
                'Efectos' => 'fa-wand-magic-sparkles',
                'Sonidos' => 'fa-music',
            ];

            $category = FurniCategory::query()->firstOrCreate(
                ['name' => $categoryName],
                ['icon' => $icons[$categoryName] ?? 'fa-couch']
            );

            $cache[$categoryName] = (int) $category->id;
        }

        return $cache[$categoryName];
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (Str::contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function buildCandidateUrls(string $baseUrl, string $hotel, int $page): array
    {
        $templates = collect(array_merge([$baseUrl], self::FALLBACK_BASE_URLS))
            ->filter()
            ->unique()
            ->values();

        return $templates
            ->map(fn (string $template) => $this->withHotelAndPage($template, $hotel, $page))
            ->unique()
            ->values()
            ->all();
    }

    private function withHotelAndPage(string $template, string $hotel, int $page): string
    {
        $url = str_replace(['__HOTEL__', '__PAGE__'], [$hotel, (string) $page], $template);
        $parts = parse_url($url);

        if ($parts === false || empty($parts['host'])) {
            return $url;
        }

        $params = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $params);
        }

        $params['hotel'] = $hotel;
        $params['page'] = $page;
        $parts['query'] = http_build_query($params);

        return $this->unparseUrl($parts);
    }

    private function unparseUrl(array $parts): string
    {
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $user = $parts['user'] ?? '';
        $pass = isset($parts['pass']) ? ':' . $parts['pass'] : '';
        $auth = ($user !== '' || $pass !== '') ? $user . $pass . '@' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $scheme . $auth . $host . $port . $path . $query . $fragment;
    }
}
