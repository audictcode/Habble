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
        {--base-url=https://habbofurni.com : URL base}
        {--paths=furni,rares,ropa,animales,efectos,sonidos : Secciones separadas por coma}
        {--max-pages=1 : Máximo de páginas por sección}
        {--dry-run : Simular sin guardar}';

    protected $description = 'Importa furnis desde Habbofurni.com por secciones, con metadata';

    public function handle(): int
    {
        $baseUrl = rtrim(trim((string) $this->option('base-url')), '/');
        $maxPages = max(1, (int) $this->option('max-pages'));
        $dryRun = (bool) $this->option('dry-run');
        $paths = collect(explode(',', (string) $this->option('paths')))
            ->map(fn ($path) => trim((string) $path, " /\t\n\r\0\x0B"))
            ->filter()
            ->values()
            ->all();

        if (empty($paths)) {
            $this->error('No hay secciones para importar.');
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

        $processed = 0;
        $created = 0;
        $updated = 0;
        $categoryCache = [];

        foreach ($paths as $path) {
            $this->line("Sección: {$path}");

            for ($page = 1; $page <= $maxPages; $page++) {
                $url = $baseUrl . '/' . $path;
                if ($page > 1) {
                    $url .= '?page=' . $page;
                }

                try {
                    $response = Http::timeout(45)->get($url);
                } catch (\Throwable $exception) {
                    $this->warn("  Página {$page}: error de red ({$exception->getMessage()})");
                    continue;
                }

                if (!$response->ok()) {
                    $this->warn("  Página {$page}: HTTP {$response->status()}");
                    continue;
                }

                $items = $this->extractItems((string) $response->body(), $path, $url);
                if (count($items) === 0) {
                    $this->warn("  Página {$page}: sin items detectados");
                    continue;
                }

                $this->line("  Página {$page}: " . count($items) . " items detectados");

                foreach ($items as $item) {
                    $normalized = $this->normalizeItem($item);
                    if (!$normalized) {
                        continue;
                    }

                    $processed++;
                    $categoryId = $this->resolveCategoryId($normalized['category_hint'], $categoryCache);

                    $query = FurniValue::query()
                        ->where('source_provider', 'habbofurni')
                        ->where('name', $normalized['name']);

                    if (filled($normalized['habbofurni_item_id'])) {
                        $query->orWhere('habbofurni_item_id', $normalized['habbofurni_item_id']);
                    }

                    $existing = $query->first();

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
            }
        }

        $mode = $dryRun ? 'SIMULACIÓN' : 'IMPORTACIÓN';
        $this->info("{$mode} Habbofurni finalizada. Procesados: {$processed}, creados: {$created}, actualizados: {$updated}");
        return self::SUCCESS;
    }

    private function extractItems(string $html, string $section, string $url): array
    {
        $items = [];

        preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/i', $html, $matches, PREG_SET_ORDER);
        foreach ($matches as $index => $match) {
            $src = $match[1] ?? null;
            if (!$src) {
                continue;
            }

            if (!Str::contains(strtolower($src), ['furni', 'item', 'asset', 'catalog'])) {
                continue;
            }

            $name = 'Item ' . ($index + 1);
            if (preg_match('/alt="([^"]+)"/i', $match[0], $alt)) {
                $name = trim((string) ($alt[1] ?? $name));
            }

            $itemId = null;
            if (preg_match('/(\d{3,})/', $src, $idMatch)) {
                $itemId = (string) $idMatch[1];
            }

            if (Str::startsWith($src, '//')) {
                $src = 'https:' . $src;
            } elseif (!Str::startsWith($src, ['http://', 'https://'])) {
                $src = 'https://habbofurni.com/' . ltrim($src, '/');
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

        return collect($items)->unique(fn ($item) => $item['image'])->values()->all();
    }

    private function normalizeItem(array $item): ?array
    {
        $name = trim((string) ($item['name'] ?? ''));
        $image = trim((string) ($item['image'] ?? ''));

        if ($name === '' || $image === '') {
            return null;
        }

        return [
            'name' => $name,
            'image' => $image,
            'item_id' => $item['item_id'] ?? null,
            'category_hint' => $item['category_hint'] ?? 'furni',
            'section' => $item['section'] ?? 'furni',
            'source_url' => $item['source_url'] ?? null,
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
}
