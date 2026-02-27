<?php

namespace App\Filament\Resources\Academy\BadgeResource\Pages;

use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Academy\BadgeResource;

class EditBadge extends EditRecord
{
    protected static string $resource = BadgeResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
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
