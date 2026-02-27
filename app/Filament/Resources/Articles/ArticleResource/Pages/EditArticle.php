<?php

namespace App\Filament\Resources\Articles\ArticleResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Filament\Facades\Filament;
use App\Filament\Resources\Articles\ArticleResource;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Filament::auth()->user();

        $data['status'] = true;
        $data['reviewed'] = true;
        $data['reviewer'] = optional($user)->username;
        $data['user_id'] = $data['user_id'] ?? optional($user)->id;

        return $data;
    }
}
