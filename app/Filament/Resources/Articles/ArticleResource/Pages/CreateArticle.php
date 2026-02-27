<?php

namespace App\Filament\Resources\Articles\ArticleResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;
use App\Filament\Resources\Articles\ArticleResource;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Filament::auth()->user();

        $data['status'] = true;
        $data['reviewed'] = true;
        $data['reviewer'] = optional($user)->username;
        $data['user_id'] = optional($user)->id;

        return $data;
    }
}
