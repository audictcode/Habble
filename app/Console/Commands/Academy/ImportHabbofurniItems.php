<?php

namespace App\Console\Commands\Academy;

use App\Models\Furni\FurniCategory;
use App\Models\FurniValue;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportHabbofurniItems extends Command
{
    protected $signature = 'furnis:import-habbofurni
        {--base-url=https://www.habbofurni.com : URL base}
        {--paths=furniture : Secciones separadas por coma}
        {--max-pages=0 : Máximo de páginas por sección (0 = sin límite)}
        {--dry-run : Simular sin guardar}';

    protected $description = 'Importa furnis desde Habbofurni.com por secciones, con metadata';

    private bool $sslBypassWarned = false;

    public function handle(): int
    {
        $baseUrl = rtrim(trim((string) $this->option('base-url')), '/');
        $maxPages = max(0, (int) $this->option('max-pages'));
        $dryRun = (bool) $this->option('dry-run');

        $sections = collect(explode(',', (string) $this->option('paths')))
            ->map(fn ($path) => trim((string) $path, " /\t\n\r\0\x0B"))
            ->filter()
            ->map(function (string $rawPath): array {
                $endpoint = $this->normalizePathForEndpoint($rawPath);
                return [
                    'hint' => $rawPath,
                    'endpoint' => $endpoint,
                ];
            })
            ->unique('endpoint')
            ->values()
            ->all();

        if (empty($sections)) {
            $sections = [
                ['hint' => 'furniture', 'endpoint' => 'furniture'],
            ];
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

        $processed = 0;
        $created = 0;
        $updated = 0;
        $categoryCache = [];

        foreach ($sections as $section) {
            $sectionHint = trim((string) ($section['hint'] ?? 'furniture'));
            $endpoint = trim((string) ($section['endpoint'] ?? 'furniture'));
            $this->line("Sección: {$sectionHint} ({$endpoint})");

            $page = 1;
            $seenPageFingerprints = [];

            while (true) {
                if ($maxPages > 0 && $page > $maxPages) {
                    break;
                }

                $url = $this->buildPageUrl($baseUrl, $endpoint, $page);

                try {
                    $response = $this->httpHtmlGet($url);
                } catch (\Throwable $exception) {
                    $this->warn("  Página {$page}: error de red ({$exception->getMessage()})");
                    break;
                }

                if (!$response->ok()) {
                    $this->warn("  Página {$page}: HTTP {$response->status()}");
                    break;
                }

                $items = $this->extractItems((string) $response->body(), $sectionHint, $url);
                if (count($items) === 0) {
                    $this->line("  Página {$page}: sin items detectados (fin de páginas)");
                    break;
                }

                $pageFingerprint = $this->buildPageFingerprint($items);
                if (isset($seenPageFingerprints[$pageFingerprint])) {
                    $this->line("  Página {$page}: resultados repetidos detectados (fin de páginas)");
                    break;
                }
                $seenPageFingerprints[$pageFingerprint] = true;

                $this->line("  Página {$page}: " . count($items) . " items detectados");

                foreach ($items as $item) {
                    $normalized = $this->normalizeItem($item, $sectionHint);
                    if (!$normalized) {
                        continue;
                    }

                    $processed++;
                    $categoryId = $this->resolveCategoryId($normalized['category_hint'], $categoryCache);

                    $existing = FurniValue::query()
                        ->where('source_provider', 'habbofurni')
                        ->where(function ($query) use ($normalized) {
                            $query->where('name', $normalized['name']);
                            if (filled($normalized['item_id'])) {
                                $query->orWhere('habbofurni_item_id', $normalized['item_id']);
                            }
                        })
                        ->first();

                    $payload = [
                        'name' => $normalized['name'],
                        'image_path' => $normalized['image'],
                        'icon_path' => $normalized['image'],
                        'category_id' => $categoryId,
                        'source_provider' => 'habbofurni',
                        'habbofurni_item_id' => $normalized['item_id'],
                        'habbofurni_imported_at' => now(),
                        'external_metadata' => [
                            'provider' => 'habbofurni',
                            'source_url' => $normalized['source_url'],
                            'section' => $normalized['section'],
                        ],
                    ];

                    if ($existing) {
                        if (!$dryRun) {
                            $existing->fill($payload)->save();
                        }
                        $updated++;
                    } else {
                        if (!$dryRun) {
                            FurniValue::query()->create($payload + [
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
        $this->info("{$mode} Habbofurni finalizada. Procesados: {$processed}, creados: {$created}, actualizados: {$updated}");
        return self::SUCCESS;
    }

    private function normalizePathForEndpoint(string $path): string
    {
        $path = strtolower(trim($path, " /\t\n\r\0\x0B"));
        if ($path === '') {
            return 'furniture';
        }

        return match ($path) {
            'furni', 'rares', 'ropa', 'animales', 'efectos', 'sonidos' => 'furniture',
            default => $path,
        };
    }

    private function buildPageUrl(string $baseUrl, string $endpoint, int $page): string
    {
        $endpoint = trim($endpoint, '/');
        $url = rtrim($baseUrl, '/') . '/' . ($endpoint !== '' ? $endpoint : 'furniture');

        if ($page <= 1) {
            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return $url . (str_contains($url, '?') ? '&' : '?') . 'page=' . $page;
        }

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }
        $query['page'] = $page;
        $parts['query'] = http_build_query($query);

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

    private function extractItems(string $html, string $section, string $url): array
    {
        $items = [];

        $decoded = json_decode($html, true);
        if (is_array($decoded)) {
            $items = array_merge($items, $this->extractFromArray($decoded));
        }

        if (preg_match('/<script[^>]*id="__NEXT_DATA__"[^>]*>(.*?)<\/script>/si', $html, $matches)) {
            $nextJson = html_entity_decode((string) ($matches[1] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $nextDecoded = json_decode($nextJson, true);
            if (is_array($nextDecoded)) {
                $items = array_merge($items, $this->extractFromArray($nextDecoded));
            }
        }

        preg_match_all('/<script[^>]*type="application\/json"[^>]*>(.*?)<\/script>/si', $html, $jsonScripts, PREG_SET_ORDER);
        foreach ($jsonScripts as $scriptBlock) {
            $scriptJson = html_entity_decode((string) ($scriptBlock[1] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $scriptDecoded = json_decode($scriptJson, true);
            if (is_array($scriptDecoded)) {
                $items = array_merge($items, $this->extractFromArray($scriptDecoded));
            }
        }

        preg_match_all('/<a[^>]+href="([^"]*\/furniture\/(\d+)[^"]*)"[^>]*>(.*?)<\/a>/is', $html, $linkMatches, PREG_SET_ORDER);
        foreach ($linkMatches as $match) {
            $link = (string) ($match[1] ?? '');
            $itemId = (string) ($match[2] ?? '');
            $block = (string) ($match[3] ?? '');

            $image = null;
            $name = null;
            if (preg_match('/<img[^>]+src="([^"]+)"/i', $block, $imgMatch)) {
                $image = (string) ($imgMatch[1] ?? '');
            }
            if (preg_match('/<img[^>]+alt="([^"]+)"/i', $block, $altMatch)) {
                $name = trim((string) ($altMatch[1] ?? ''));
            }

            if (!$image) {
                continue;
            }

            if (Str::startsWith($image, '//')) {
                $image = 'https:' . $image;
            } elseif (!Str::startsWith($image, ['http://', 'https://'])) {
                $image = rtrim($url, '/') . '/' . ltrim($image, '/');
            }

            if (Str::startsWith($link, '//')) {
                $link = 'https:' . $link;
            } elseif (!Str::startsWith($link, ['http://', 'https://'])) {
                $link = rtrim($url, '/') . '/' . ltrim($link, '/');
            }

            $items[] = [
                'name' => $name ?: ('Item ' . $itemId),
                'image' => $image,
                'item_id' => $itemId !== '' ? $itemId : null,
                'category_hint' => $section,
                'section' => $section,
                'source_url' => $link ?: $url,
            ];
        }

        preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/i', $html, $imageMatches, PREG_SET_ORDER);
        foreach ($imageMatches as $index => $match) {
            $src = (string) ($match[1] ?? '');
            if ($src === '') {
                continue;
            }

            $srcLower = strtolower($src);
            if (!Str::contains($srcLower, ['furni', 'furniture', 'habbofurni', 'item', 'catalog'])) {
                continue;
            }

            $name = 'Item ' . ($index + 1);
            if (preg_match('/alt="([^"]+)"/i', $match[0], $alt)) {
                $candidate = trim((string) ($alt[1] ?? ''));
                if ($candidate !== '') {
                    $name = $candidate;
                }
            }

            $itemId = null;
            if (preg_match('/(\d{3,})/', $src, $idMatch)) {
                $itemId = (string) $idMatch[1];
            }

            if (Str::startsWith($src, '//')) {
                $src = 'https:' . $src;
            } elseif (!Str::startsWith($src, ['http://', 'https://'])) {
                $src = rtrim($url, '/') . '/' . ltrim($src, '/');
            }

            $items[] = [
                'name' => $name,
                'image' => $src,
                'item_id' => $itemId,
                'category_hint' => $section,
                'section' => $section,
                'source_url' => $url,
            ];
        }

        return collect($items)
            ->map(function (array $item) use ($section, $url): array {
                $item['section'] = $item['section'] ?? $section;
                $item['category_hint'] = $item['category_hint'] ?? $section;
                $item['source_url'] = $item['source_url'] ?? $url;
                return $item;
            })
            ->unique(fn ($item) => implode('|', [
                trim((string) ($item['item_id'] ?? '')),
                trim((string) ($item['name'] ?? '')),
                trim((string) ($item['image'] ?? '')),
            ]))
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
        $hasName = isset($node['name']) || isset($node['title']) || isset($node['classname']) || isset($node['furniName']) || isset($node['public_name']);
        $hasImage = isset($node['image']) || isset($node['image_url']) || isset($node['icon']) || isset($node['iconUrl']) || isset($node['thumbnail']) || isset($node['imagePath']) || isset($node['icon_path']);
        $hasId = isset($node['id']) || isset($node['furni_id']) || isset($node['item_id']) || isset($node['slug']) || isset($node['identifier']);

        return ($hasName && $hasImage) || ($hasImage && $hasId);
    }

    private function normalizeItem(array $item, string $defaultSection): ?array
    {
        $name = trim((string) (
            $item['name']
            ?? $item['title']
            ?? $item['classname']
            ?? $item['furniName']
            ?? $item['public_name']
            ?? ''
        ));

        $image = $item['image']
            ?? $item['image_url']
            ?? $item['thumbnail']
            ?? $item['icon']
            ?? $item['iconUrl']
            ?? $item['imagePath']
            ?? $item['icon_path']
            ?? null;

        if (is_array($image)) {
            $image = $image['url'] ?? $image['src'] ?? reset($image);
        }

        $image = trim((string) $image);

        $sourceId = $item['id'] ?? $item['furni_id'] ?? $item['item_id'] ?? $item['identifier'] ?? null;
        $sourceId = is_scalar($sourceId) ? trim((string) $sourceId) : null;

        if ($name === '' && $sourceId) {
            $name = 'Item ' . $sourceId;
        }

        if ($name === '' || $image === '') {
            return null;
        }

        if (Str::startsWith($image, '//')) {
            $image = 'https:' . $image;
        } elseif (!Str::startsWith($image, ['http://', 'https://'])) {
            $image = 'https://www.habbofurni.com/' . ltrim($image, '/');
        }

        $hints = collect([
            $defaultSection,
            $item['category'] ?? null,
            $item['section'] ?? null,
            $item['type'] ?? null,
            $item['rarity'] ?? null,
            $item['state'] ?? null,
            $name,
        ])->filter()->map(fn ($value) => strtolower(trim((string) $value)))->implode(' ');

        return [
            'name' => $name,
            'image' => $image,
            'item_id' => $sourceId !== '' ? $sourceId : null,
            'category_hint' => $hints !== '' ? $hints : strtolower($defaultSection),
            'section' => trim((string) ($item['section'] ?? $defaultSection)),
            'source_url' => trim((string) ($item['source_url'] ?? $item['url'] ?? '')) ?: null,
        ];
    }

    private function resolveCategoryId(string $categoryHint, array &$cache): int
    {
        $hint = strtolower($categoryHint);
        $name = 'Furnis normales';

        if (Str::contains($hint, 'rare')) {
            $name = 'Rares';
        } elseif (Str::contains($hint, ['ropa', 'clothing'])) {
            $name = 'Ropa';
        } elseif (Str::contains($hint, ['animal', 'pet'])) {
            $name = 'Animales';
        } elseif (Str::contains($hint, ['efecto', 'effect'])) {
            $name = 'Efectos';
        } elseif (Str::contains($hint, ['sonido', 'sound', 'music'])) {
            $name = 'Sonidos';
        }

        if (!isset($cache[$name])) {
            $icons = [
                'Rares' => 'fa-gem',
                'Furnis normales' => 'fa-couch',
                'Ropa' => 'fa-shirt',
                'Animales' => 'fa-paw',
                'Efectos' => 'fa-wand-magic-sparkles',
                'Sonidos' => 'fa-music',
            ];

            $category = FurniCategory::query()->firstOrCreate(
                ['name' => $name],
                ['icon' => $icons[$name] ?? 'fa-couch']
            );

            $cache[$name] = (int) $category->id;
        }

        return $cache[$name];
    }

    private function buildPageFingerprint(array $items): string
    {
        $identity = array_values(array_map(static function (array $item): string {
            return implode('|', [
                trim((string) ($item['item_id'] ?? $item['id'] ?? '')),
                trim((string) ($item['name'] ?? $item['title'] ?? '')),
                trim((string) ($item['image'] ?? $item['image_url'] ?? '')),
            ]);
        }, $items));

        return md5(json_encode($identity));
    }

    private function httpHtmlGet(string $url)
    {
        return $this->httpGetWithSslFallback($url, 'text/html,application/xhtml+xml');
    }

    private function httpGetWithSslFallback(string $url, string $accept)
    {
        $attempt = 0;
        $maxAttempts = 4;
        $withoutVerifying = false;

        while ($attempt < $maxAttempts) {
            $attempt++;

            try {
                $request = Http::timeout(45)
                    ->accept($accept)
                    ->withHeaders([
                        'User-Agent' => 'HabbleHK/1.0 (+https://habble.org)',
                    ]);

                if ($withoutVerifying) {
                    $request = $request->withoutVerifying();
                }

                $response = $request->get($url);

                if ($this->shouldRetryResponse($response) && $attempt < $maxAttempts) {
                    $waitSeconds = $this->retryWaitSeconds($attempt);
                    $this->warn("  HTTP {$response->status()} en {$url}. Reintentando en {$waitSeconds}s...");
                    sleep($waitSeconds);
                    continue;
                }

                return $response;
            } catch (\Throwable $exception) {
                if ($this->isSslCertificateIssue($exception) && !$withoutVerifying) {
                    if (!$this->sslBypassWarned) {
                        $this->warn('SSL local no configurado (cURL 60). Reintentando sin verificación de certificado para este comando.');
                        $this->sslBypassWarned = true;
                    }

                    $withoutVerifying = true;
                    if ($attempt < $maxAttempts) {
                        continue;
                    }
                }

                if ($attempt >= $maxAttempts) {
                    throw $exception;
                }

                $waitSeconds = $this->retryWaitSeconds($attempt);
                $this->warn("  Error de red en {$url}. Reintentando en {$waitSeconds}s...");
                sleep($waitSeconds);
            }
        }

        return Http::timeout(45)->accept($accept)->get($url);
    }

    private function shouldRetryResponse(mixed $response): bool
    {
        if (!is_object($response) || !method_exists($response, 'status')) {
            return false;
        }

        $status = (int) $response->status();

        return in_array($status, [429, 500, 502, 503, 504, 522, 524], true);
    }

    private function retryWaitSeconds(int $attempt): int
    {
        return min(12, max(2, $attempt * 2));
    }

    private function isSslCertificateIssue(\Throwable $exception): bool
    {
        $message = strtolower((string) $exception->getMessage());

        return str_contains($message, 'curl error 60')
            || str_contains($message, 'ssl certificate problem')
            || str_contains($message, 'unable to get local issuer certificate');
    }
}
