<?php

namespace App\Filament\Resources\Furni\FurniCategoryResource\Pages;

use App\Filament\Resources\Furni\FurniCategoryResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EditFurniCategory extends EditRecord
{
    protected static string $resource = FurniCategoryResource::class;

    public function mount($record): void
    {
        try {
            parent::mount($record);
        } catch (ModelNotFoundException $exception) {
            Notification::make()
                ->title('Categoría no encontrada')
                ->warning()
                ->send();

            $this->redirect(FurniCategoryResource::getUrl('index'));
        }
    }
}
