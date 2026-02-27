<?php

namespace App\Filament\Resources\Academy\BadgeResource\Pages;

use App\Filament\Resources\Academy\BadgeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBadge extends CreateRecord
{
    protected static string $resource = BadgeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!filled($data['image_path'] ?? null)) {
            $resolvedPath = $this->resolveBadgeAssetUrl($data['code'] ?? '');
            if ($resolvedPath) {
                $data['image_path'] = $resolvedPath;
            }
        }

        return $data;
    }

    private function resolveBadgeAssetUrl(string $code): ?string
    {
        $normalizedCode = strtoupper(trim($code));
        if ($normalizedCode === '') {
            return null;
        }

        return 'https://www.habboassets.com/assets/badges/' . urlencode($normalizedCode) . '.gif';
    }
}
