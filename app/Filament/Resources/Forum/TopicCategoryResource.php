<?php

namespace App\Filament\Resources\Forum;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use App\Models\Topic\TopicCategory;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use App\Filament\Resources\Forum\TopicCategoryResource\Pages;

class TopicCategoryResource extends Resource
{
    protected static ?string $model = TopicCategory::class;

    protected static ?string $slug = 'forum/categories';

    protected static ?string $navigationGroup = 'Foro';

    protected static ?string $navigationLabel = 'Gestionar Categorias';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome')
                        ->required(),

                    Forms\Components\FileUpload::make('icon')
                        ->label('Ícone da Categoria')
                        ->directory('topics_categories')
                        ->hint('<strong>Predeterminado:</strong> Sem ícone')
                        ->helperText('PS: Espere carregar a imagem para salvar a categoria.')
                        ->image(),
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

                Tables\Columns\ImageColumn::make('icon')
                    ->label('Ícone')
                    ->size(20),

                Tables\Columns\TextColumn::make('topics_count')
                    ->extraAttributes(['class' => 'font-bold'])
                    ->label('Contagem de Temas')
                    ->counts('topics')
            ])
            ->filters([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TopicCategoryResource\RelationManagers\TopicsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTopicCategories::route('/'),
            'create' => Pages\CreateTopicCategory::route('/create'),
            'view' => Pages\ViewTopicCategory::route('/{record}'),
            'edit' => Pages\EditTopicCategory::route('/{record}/edit'),
        ];
    }
}
