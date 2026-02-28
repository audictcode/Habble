<?php

namespace App\Console\Commands\Academy;

use App\Models\Badge;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportHabboAssetsBadges extends Command
{
    private const DEFAULT_BASE_URL = 'https://www.habboassets.com/api/v1/badges';
    private const DEFAULT_WEB_BASE_URL = 'https://www.habboassets.com';
    private bool $sslBypassWarned = false;

    protected $signature = 'badges:import-habboassets
        {--base-url= : Endpoint JSON de badges}
        {--web-base-url= : Base URL web para extraer metadata HTML}
        {--sync-latest-web : Sincroniza últimas placas desde listado web /badges}
        {--web-pages=2 : Cantidad de páginas del listado web a revisar}
        {--all : Importar todas las páginas disponibles}
        {--limit=2000 : Registros por página (máx recomendado 2000)}
        {--offset=0 : Offset inicial}
        {--max-pages=0 : Máximo de páginas (0 = sin límite)}
        {--hotel= : Filtrar por hotel (es, com, com.br, etc)}
        {--skip-html-metadata : No consultar metadata extra desde HTML de detalle}
        {--dry-run : Simular sin escribir en base de datos}';

    protected $description = 'Importa badges desde HabboAssets API a la tabla badges';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $offset = max(0, (int) $this->option('offset'));
        $maxPages = max(0, (int) $this->option('max-pages'));
        $baseUrl = trim((string) $this->option('base-url'));
        if ($baseUrl === '') {
            $baseUrl = self::DEFAULT_BASE_URL;
        }
        $webBaseUrl = trim((string) $this->option('web-base-url'));
        if ($webBaseUrl === '') {
            $webBaseUrl = self::DEFAULT_WEB_BASE_URL;
        }
        $hotel = trim((string) $this->option('hotel'));
        $dryRun = (bool) $this->option('dry-run');
        $importAll = (bool) $this->option('all');
        $useHtmlMetadata = !((bool) $this->option('skip-html-metadata'));
        $syncLatestWeb = (bool) $this->option('sync-latest-web');
        $webPages = max(1, (int) $this->option('web-pages'));

        if ($syncLatestWeb) {
            return $this->syncLatestFromWeb($webBaseUrl, $webPages, $dryRun);
        }

        $this->info('Importando badges desde HabboAssets...');
        $page = 0;
        $processed = 0;
        $created = 0;
        $updated = 0;

        while (true) {
            $page++;

            if ($maxPages > 0 && $page > $maxPages) {
                break;
            }

            $query = [
                'limit' => $limit,
                'offset' => $offset,
            ];

            if ($hotel !== '') {
                $query['hotel'] = $hotel;
            }

            $candidateUrls = collect([
                $baseUrl,
                self::DEFAULT_BASE_URL,
                'https://habboassets.com/api/v1/badges',
            ])->unique()->values()->all();

            $response = null;
            $lastError = null;

            foreach ($candidateUrls as $candidateUrl) {
                try {
                    $response = $this->httpJsonGet($candidateUrl, $query);

                    if ($response->ok()) {
                        $baseUrl = $candidateUrl;
                        break;
                    }

                    $lastError = 'HTTP ' . $response->status() . ' endpoint=' . $candidateUrl;
                } catch (\Throwable $exception) {
                    $lastError = $exception->getMessage();
                }
            }

            if (!$response || !$response->ok()) {
                $this->error('Error de red HabboAssets: ' . ($lastError ?: 'sin respuesta'));
                return self::FAILURE;
            }

            $payload = $response->json();
            $badges = $this->extractBadgeList($payload);
            $count = count($badges);

            if ($count === 0) {
                $this->info('No hay más resultados.');
                break;
            }

            $this->line("Página {$page}: {$count} badges (offset {$offset})");

            foreach ($badges as $item) {
                $code = trim((string) ($item['code'] ?? ''));
                if ($code === '') {
                    continue;
                }

                $sourceId = isset($item['id']) ? (int) $item['id'] : null;
                $sourceHotel = isset($item['hotel']) ? strtolower((string) $item['hotel']) : null;
                $sourceCreatedAt = !empty($item['created_at']) ? Carbon::parse($item['created_at']) : null;
                $sourceUpdatedAt = !empty($item['updated_at']) ? Carbon::parse($item['updated_at']) : null;

                $apiTitle = $this->firstFilledString([
                    $item['title'] ?? null,
                    $item['name'] ?? null,
                    $item['badge_name'] ?? null,
                    $item['code'] ?? null,
                ]);
                $apiDescription = $this->sanitizeMetadataText($this->firstFilledString([
                    $item['description'] ?? null,
                    $item['desc'] ?? null,
                    $item['badge_desc'] ?? null,
                ]));

                $attributes = [
                    'title' => $apiTitle ?: (string) ($item['code'] ?? $code),
                    'description' => $apiDescription,
                    'code' => $code,
                    'habboassets_badge_id' => $sourceId,
                    'habboassets_hotel' => $sourceHotel,
                    'habboassets_source_created_at' => $sourceCreatedAt,
                    'habboassets_source_updated_at' => $sourceUpdatedAt,
                    'imported_from_habboassets_at' => now(),
                    'image_path' => 'https://www.habboassets.com/assets/badges/' . urlencode($code) . '.gif',
                    'rarity' => 'normal',
                    'content_slug' => 'https://www.habboassets.com/badges/' . ($sourceId ?: $code),
                    'habbo_published_at' => $sourceCreatedAt,
                    'published_at' => now(),
                ];

                if ($useHtmlMetadata) {
                    $metadata = $this->fetchBadgeHtmlMetadata(
                        $sourceId,
                        $code,
                        $webBaseUrl
                    );

                    if (filled($metadata['title'] ?? null)) {
                        $attributes['title'] = $metadata['title'];
                    }

                    if (array_key_exists('description', $metadata)) {
                        $attributes['description'] = $this->sanitizeMetadataText($metadata['description']);
                    }

                    if (filled($metadata['hotel'] ?? null)) {
                        $attributes['habboassets_hotel'] = $this->normalizeHotel((string) $metadata['hotel']);
                    }

                    if (
                        !$sourceCreatedAt
                        && !empty($metadata['found_at'])
                        && $metadata['found_at'] instanceof Carbon
                    ) {
                        $attributes['habboassets_source_created_at'] = $metadata['found_at'];
                        $attributes['habbo_published_at'] = $metadata['found_at'];
                    }

                    if (filled($metadata['detail_url'] ?? null)) {
                        $attributes['content_slug'] = $metadata['detail_url'];
                    }
                }

                $attributes['description'] = $this->sanitizeMetadataText($attributes['description'] ?? null);

                $existing = Badge::query()
                    ->where('code', $code)
                    ->when($sourceId, fn ($query) => $query->orWhere('habboassets_badge_id', $sourceId))
                    ->first();

                if ($existing) {
                    $processed++;
                    if (!$dryRun) {
                        $existing->fill(array_filter($attributes, fn ($value) => $value !== null))->save();
                    }
                    $updated++;
                    continue;
                }

                $processed++;
                if (!$dryRun) {
                    Badge::query()->create($attributes);
                }
                $created++;
            }

            $offset += $limit;

            if (!$importAll) {
                break;
            }
        }

        $mode = $dryRun ? 'SIMULACIÓN' : 'IMPORTACIÓN';
        $this->info("{$mode} terminada. Procesados: {$processed}, creados: {$created}, actualizados: {$updated}");

        return self::SUCCESS;
    }

    private function syncLatestFromWeb(string $webBaseUrl, int $pages, bool $dryRun): int
    {
        $this->info("Sincronizando últimas placas desde {$webBaseUrl}/badges ...");

        $badgeIds = collect();

        for ($page = 1; $page <= $pages; $page++) {
            $url = rtrim($webBaseUrl, '/') . '/badges';
            if ($page > 1) {
                $url .= '?page=' . $page;
            }

            try {
                $response = $this->httpHtmlGet($url);
            } catch (\Throwable $exception) {
                $this->warn("No se pudo consultar {$url}: " . $exception->getMessage());
                continue;
            }

            if (!$response->ok() || !is_string($response->body()) || $response->body() === '') {
                $this->warn("Respuesta inválida en {$url}");
                continue;
            }

            preg_match_all('/href=["\'](?:https?:\/\/(?:www\.)?habboassets\.com)?\/badges\/(\d+)["\']/i', $response->body(), $matches);
            $idsInPage = collect($matches[1] ?? [])->map(fn ($id) => (int) $id)->filter()->values();

            $this->line("Página web {$page}: " . $idsInPage->count() . ' referencias encontradas');
            $badgeIds = $badgeIds->merge($idsInPage);
        }

        $badgeIds = $badgeIds->unique()->values();

        if ($badgeIds->isEmpty()) {
            $this->error('No se encontraron referencias de badges en el listado web.');
            return self::FAILURE;
        }

        $processed = 0;
        $created = 0;
        $updated = 0;

        foreach ($badgeIds as $sourceId) {
            $metadata = $this->fetchBadgeHtmlMetadata((int) $sourceId, '', $webBaseUrl);
            $code = strtoupper(trim((string) ($metadata['code'] ?? '')));

            if ($code === '') {
                $this->warn("Sin código detectable para badge ID {$sourceId}, omitido.");
                continue;
            }

            $sourceCreatedAt = !empty($metadata['found_at']) && $metadata['found_at'] instanceof Carbon
                ? $metadata['found_at']
                : null;

            $attributes = [
                'title' => filled($metadata['title'] ?? null) ? (string) $metadata['title'] : $code,
                'description' => $this->sanitizeMetadataText($metadata['description'] ?? null),
                'code' => $code,
                'habboassets_badge_id' => (int) $sourceId,
                'habboassets_hotel' => filled($metadata['hotel'] ?? null)
                    ? $this->normalizeHotel((string) $metadata['hotel'])
                    : null,
                'habboassets_source_created_at' => $sourceCreatedAt,
                'imported_from_habboassets_at' => now(),
                'image_path' => 'https://www.habboassets.com/assets/badges/' . urlencode($code) . '.gif',
                'rarity' => 'normal',
                'content_slug' => filled($metadata['detail_url'] ?? null)
                    ? (string) $metadata['detail_url']
                    : 'https://www.habboassets.com/badges/' . $sourceId,
                'habbo_published_at' => $sourceCreatedAt,
                'published_at' => now(),
            ];

            $existing = Badge::query()
                ->where('habboassets_badge_id', (int) $sourceId)
                ->orWhere('code', $code)
                ->first();

            if ($existing) {
                $processed++;
                if (!$dryRun) {
                    $existing->fill(array_filter($attributes, fn ($value) => $value !== null))->save();
                }
                $updated++;
                continue;
            }

            $processed++;
            if (!$dryRun) {
                Badge::query()->create($attributes);
            }
            $created++;
        }

        $mode = $dryRun ? 'SIMULACIÓN WEB' : 'SYNC WEB';
        $this->info("{$mode} terminada. Procesados: {$processed}, creados: {$created}, actualizados: {$updated}");

        return self::SUCCESS;
    }

    private function fetchBadgeHtmlMetadata(?int $sourceId, string $code, string $webBaseUrl): array
    {
        $code = strtoupper(trim($code));

        $candidateUrls = collect([
            $sourceId ? rtrim($webBaseUrl, '/') . '/badges/' . $sourceId : null,
            'https://www.habboassets.com/badges/' . ($sourceId ?: $code),
            'https://habboassets.com/badges/' . ($sourceId ?: $code),
        ])->filter()->unique()->values()->all();

        $html = null;
        $resolvedUrl = null;

        foreach ($candidateUrls as $url) {
            try {
                $response = $this->httpHtmlGet($url);

                if ($response->ok() && is_string($response->body()) && $response->body() !== '') {
                    $html = $response->body();
                    $resolvedUrl = $url;
                    break;
                }
            } catch (\Throwable $exception) {
                // Continuar con siguiente URL candidata
            }
        }

        if (!is_string($html) || $html === '') {
            return [];
        }

        $resolvedCode = $code !== '' ? $code : (string) ($this->extractBadgeCodeFromHtml($html) ?? '');
        $name = $resolvedCode !== '' ? $this->extractClientTextValue($html, 'badge_name_' . $resolvedCode) : null;
        $desc = $resolvedCode !== '' ? $this->extractClientTextValue($html, 'badge_desc_' . $resolvedCode) : null;

        if (!filled($name)) {
            $name = $this->extractFirstMatch($html, '/<h2[^>]*>\s*([^<]+)\s*<\/h2>/i');
        }

        $hotel = $this->extractInfoField($html, 'Hotel');
        $foundRaw = $this->extractInfoField($html, 'Found');
        $foundAt = $this->parseRelativeFoundDate($foundRaw);

        return [
            'title' => filled($name) ? $name : null,
            'description' => $desc ?? null,
            'code' => $resolvedCode !== '' ? $resolvedCode : null,
            'hotel' => $hotel,
            'found_raw' => $foundRaw,
            'found_at' => $foundAt,
            'detail_url' => $resolvedUrl,
        ];
    }

    private function extractClientTextValue(string $html, string $key): ?string
    {
        $pattern = '/\b' . preg_quote($key, '/') . '=([^\r\n<]*)/i';
        $value = $this->extractFirstMatch($html, $pattern);
        if ($value === null) {
            return null;
        }

        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim($value);
    }

    private function extractInfoField(string $html, string $label): ?string
    {
        $pattern = '/<dt[^>]*>\s*' . preg_quote($label, '/') . '\s*<\/dt>\s*<dd[^>]*>(.*?)<\/dd>/is';
        $raw = $this->extractFirstMatch($html, $pattern);
        if ($raw === null) {
            return null;
        }

        $text = trim(html_entity_decode(strip_tags($raw), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        return $text !== '' ? $text : null;
    }

    private function extractFirstMatch(string $text, string $pattern): ?string
    {
        if (!preg_match($pattern, $text, $matches)) {
            return null;
        }

        $value = $matches[1] ?? null;
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        return $value !== '' ? $value : null;
    }

    private function extractBadgeCodeFromHtml(string $html): ?string
    {
        if (!preg_match('/assets\/badges\/([A-Za-z0-9_-]+)\.(?:gif|png|webp)/i', $html, $matches)) {
            return null;
        }

        $code = strtoupper(trim((string) ($matches[1] ?? '')));
        return $code !== '' ? $code : null;
    }

    private function parseRelativeFoundDate(?string $foundRaw): ?Carbon
    {
        if (!filled($foundRaw)) {
            return null;
        }

        $normalized = strtolower(trim((string) $foundRaw));

        if (preg_match('/^(\d+)\s+(minute|minutes|hour|hours|day|days|week|weeks|month|months|year|years)\s+ago$/i', $normalized, $matches)) {
            $amount = max(1, (int) ($matches[1] ?? 0));
            $unit = strtolower((string) ($matches[2] ?? ''));
            $now = now();

            return match ($unit) {
                'minute', 'minutes' => $now->copy()->subMinutes($amount),
                'hour', 'hours' => $now->copy()->subHours($amount),
                'day', 'days' => $now->copy()->subDays($amount),
                'week', 'weeks' => $now->copy()->subWeeks($amount),
                'month', 'months' => $now->copy()->subMonths($amount),
                'year', 'years' => $now->copy()->subYears($amount),
                default => null,
            };
        }

        return null;
    }

    private function extractBadgeList(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        $candidates = [
            $payload['badges'] ?? null,
            $payload['data'] ?? null,
            $payload['results'] ?? null,
            $payload,
        ];

        foreach ($candidates as $candidate) {
            if (!is_array($candidate) || $candidate === []) {
                continue;
            }

            $first = reset($candidate);
            if (is_array($first) && (isset($first['code']) || isset($first['id']))) {
                return array_values(array_filter($candidate, 'is_array'));
            }
        }

        return [];
    }

    private function firstFilledString(array $values): ?string
    {
        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }

            $value = trim($value);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function sanitizeMetadataText(?string $text): ?string
    {
        if (!is_string($text)) {
            return null;
        }

        $text = trim($text);
        if ($text === '') {
            return null;
        }

        $localPathMarkers = [
            '/applications/mamp/htdocs',
            '\\applications\\mamp\\htdocs',
            '/var/www/',
            '\\xampp\\htdocs',
        ];

        $normalized = strtolower($text);
        foreach ($localPathMarkers as $marker) {
            if (str_contains($normalized, $marker)) {
                return null;
            }
        }

        return $text;
    }

    private function httpJsonGet(string $url, array $query = [])
    {
        return $this->httpGetWithSslFallback($url, $query, 'application/json');
    }

    private function httpHtmlGet(string $url, array $query = [])
    {
        return $this->httpGetWithSslFallback($url, $query, 'text/html,application/xhtml+xml');
    }

    private function httpGetWithSslFallback(string $url, array $query, string $accept)
    {
        try {
            return Http::timeout(45)
                ->accept($accept)
                ->get($url, $query);
        } catch (\Throwable $exception) {
            if (!$this->isSslCertificateIssue($exception)) {
                throw $exception;
            }

            if (!$this->sslBypassWarned) {
                $this->warn('SSL local no configurado (cURL 60). Reintentando sin verificación de certificado para este comando.');
                $this->sslBypassWarned = true;
            }

            return Http::withoutVerifying()
                ->timeout(45)
                ->accept($accept)
                ->get($url, $query);
        }
    }

    private function isSslCertificateIssue(\Throwable $exception): bool
    {
        $message = strtolower((string) $exception->getMessage());

        return str_contains($message, 'curl error 60')
            || str_contains($message, 'ssl certificate problem')
            || str_contains($message, 'unable to get local issuer certificate');
    }

    private function normalizeHotel(string $hotel): string
    {
        $hotel = strtolower(trim($hotel));
        $hotel = preg_replace('/\s+/', '', $hotel) ?? $hotel;

        return match ($hotel) {
            'tr' => 'com.tr',
            'br' => 'com.br',
            'us', 'en', 'com' => 'com',
            default => $hotel,
        };
    }
}
