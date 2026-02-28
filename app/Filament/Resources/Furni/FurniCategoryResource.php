<?php

namespace App\Filament\Resources\Furni;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use App\Models\Furni\FurniCategory;
use Filament\Forms\Components\Grid;
use App\Filament\Resources\Furni\FurniCategoryResource\Pages;

class FurniCategoryResource extends Resource
{
    protected static ?string $model = FurniCategory::class;

    protected static ?string $slug = 'furni/categories';

    protected static ?string $navigationGroup = 'Valores';

    protected static ?string $navigationLabel = 'Gestionar Categorias';

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome')
                        ->required(),

                    Forms\Components\TextInput::make('icon')
                        ->label('Ícone da Categoria')
                        ->hint('<strong>Predeterminado:</strong> fa-couch')
                        ->helperText('Puedes usar una clase de ícono (ej: fa-gem) o una ruta/URL de imagen.')
                        ->placeholder('fa-gem'),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Nome'),

                Tables\Columns\TextColumn::make('icon')
                    ->label('Ícone')
                    ->formatStateUsing(fn (?string $state): string => $state ?: '-'),

                Tables\Columns\TextColumn::make('furnis_count')
                    ->label('Contador de Mobis')
                    ->counts('furnis')
                    ->extraAttributes(['class' => 'font-bold'])
            ])
            ->filters([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            FurniCategoryResource\RelationManagers\FurnisRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFurniCategories::route('/'),
            'create' => Pages\CreateFurniCategory::route('/create'),
            'edit' => Pages\EditFurniCategory::route('/{record}/edit'),
        ];
    }
}
