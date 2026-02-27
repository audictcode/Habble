<?php

namespace App\Filament\Resources\Academy\CampaignInfoResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use App\Filament\Resources\Academy\CampaignInfoResource;

class EditCampaignInfo extends EditRecord
{
    protected static string $resource = CampaignInfoResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['banner_image_path'] = $this->normalizeBannerImagePath((string) ($data['banner_image_path'] ?? ''));

        $user = auth()->user();
        if ($user) {
            $displayName = $user->habbo_name ?: $user->username;
            $hotel = $user->habbo_hotel ?: 'es';

            $data['created_by_user_id'] = $data['created_by_user_id'] ?? $user->id;
            $data['author_name'] = $displayName;
            $data['author_avatar_url'] = 'https://www.habbo.' . $hotel . '/habbo-imaging/avatarimage?user=' . urlencode($displayName) . '&direction=2&head_direction=2&headonly=1&size=l';
        }

        $data['slug'] = $data['target_page'] ?? 'informacion-campana';

        if (($data['target_page'] ?? '') === 'informacion-campana') {
            $data['primary_button_text'] = null;
            $data['primary_button_url'] = null;
            $data['secondary_button_text'] = null;
            $data['secondary_button_url'] = null;
            $data['primary_button_color'] = $data['primary_button_color'] ?? '#0095ff';
            $data['secondary_button_color'] = $data['secondary_button_color'] ?? '#1f2937';

            $data['info_cells'] = null;
        }

        return $data;
    }

    private function normalizeBannerImagePath(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            return $value;
        }

        $path = str_replace('\\', '/', ltrim($value, '/'));

        if (Str::startsWith($path, 'public/')) {
            $path = substr($path, 7);
        }

        if (Str::startsWith($path, 'storage/')) {
            $path = substr($path, 8);
        }

        return $path;
    }
}
