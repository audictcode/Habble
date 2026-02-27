<?php

namespace App\Console\Commands\Academy;

use App\Models\Badge;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RepairBadgeMetadata extends Command
{
    protected $signature = 'badges:repair-metadata
        {--set-published : Completa published_at cuando falte}
        {--chunk=1000 : Tamaño de lote}';

    protected $description = 'Repara y normaliza metadata de badges existentes';

    public function handle(): int
    {
        $setPublished = (bool) $this->option('set-published');
        $chunk = max(100, (int) $this->option('chunk'));

        $updated = 0;
        $checked = 0;

        Badge::query()->orderBy('id')->chunk($chunk, function ($badges) use (&$updated, &$checked, $setPublished) {
            foreach ($badges as $badge) {
                $checked++;
                $dirty = false;

                $code = strtoupper(trim((string) ($badge->code ?? '')));
                if ($code === '') {
                    continue;
                }

                if (!filled($badge->title)) {
                    $badge->title = $code;
                    $dirty = true;
                }

                if (!filled($badge->image_path)) {
                    $badge->image_path = 'https://www.habboassets.com/assets/badges/' . urlencode($code) . '.gif';
                    $dirty = true;
                }

                if (!filled($badge->content_slug)) {
                    $badge->content_slug = 'https://www.habboassets.com/badges/' . urlencode($code);
                    $dirty = true;
                }

                if (!filled($badge->habboassets_hotel)) {
                    $badge->habboassets_hotel = $this->hotelFromCode($code);
                    $dirty = true;
                }

                if (!$badge->habboassets_source_created_at && $badge->habbo_published_at) {
                    $badge->habboassets_source_created_at = $badge->habbo_published_at;
                    $dirty = true;
                }

                if (!$badge->habbo_published_at && $badge->habboassets_source_created_at) {
                    $badge->habbo_published_at = $badge->habboassets_source_created_at;
                    $dirty = true;
                }

                if (!$badge->imported_from_habboassets_at) {
                    $badge->imported_from_habboassets_at = $badge->updated_at ?: now();
                    $dirty = true;
                }

                if ($setPublished) {
                    $metadataPublishedAt = $badge->habboassets_source_created_at
                        ?: $badge->habbo_published_at;

                    $targetPublishedAt = $metadataPublishedAt ?: ($badge->published_at ?: now());

                    if (
                        !$badge->published_at
                        || !$badge->published_at->equalTo($targetPublishedAt)
                    ) {
                        $badge->published_at = $targetPublishedAt;
                        $dirty = true;
                    }
                }

                if ($dirty) {
                    $badge->save();
                    $updated++;
                }
            }
        });

        $this->info("Reparación finalizada. Revisados: {$checked}, actualizados: {$updated}");
        return self::SUCCESS;
    }

    private function hotelFromCode(string $code): string
    {
        $map = [
            'ES' => 'es',
            'US' => 'com',
            'BR' => 'com.br',
            'DE' => 'de',
            'FR' => 'fr',
            'IT' => 'it',
            'NL' => 'nl',
            'FI' => 'fi',
            'TR' => 'com.tr',
            'PT' => 'com.br',
        ];

        foreach ($map as $prefix => $hotel) {
            if (Str::startsWith($code, $prefix)) {
                return $hotel;
            }
        }

        return 'global';
    }
}
