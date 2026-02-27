<?php

namespace App\Filament\Resources\Academy;

use Filament\Forms;
use Filament\Tables;
use App\Models\Slide;
use Filament\Resources\Form;
use Filament\Tables\Filters;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use App\Filament\Traits\ShowLatestResources;
use App\Filament\Resources\Academy\SlideResource\Pages;

class SlideResource extends Resource
{
    use ShowLatestResources;

    protected static ?string $model = Slide::class;

    protected static ?string $slug = 'academy/slides';

    protected static ?string $navigationGroup = 'Academy';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Gestionar Slides';

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    Forms\Components\TextInput::make('title')
                        ->maxLength(255)
                        ->required()
                        ->label('Título'),

                    Forms\Components\TextInput::make('description')
                        ->hint('<strong>Predeterminado:</strong> No tendrá descripción')
                        ->label('Descrição'),

                    Forms\Components\TextInput::make('slug')
                        ->hint('<strong>Predeterminado:</strong> No tendrá redireccionamiento')
                        ->label('Link para Navegação'),

                    Forms\Components\TextInput::make('image_path')
                        ->label('URL da Imagem')
                        ->required()
                        ->helperText('Use URL completa (https://...) o ruta local (/images/... o storage/...).'),

                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Fecha de publicación'),
                ]),

                Forms\Components\Toggle::make('active')
                    ->label('Ativo')
                    ->default(true)
                    ->hint('<strong>Predeterminado:</strong> Ativo')
                    ->helperText('Marque para aparecer no site'),

                Forms\Components\Toggle::make('fixed')
                    ->hint('<strong>Predeterminado:</strong> No fijado')
                    ->label('Slide fixo')
                    ->helperText('Sempre aparecerá primeiro dos outros'),

                Forms\Components\Toggle::make('new_tab')
                    ->hint('<strong>Predeterminado:</strong> No abrirá em uma nova guia')
                    ->label('Nova guia')
                    ->helperText('Abrirá o link em uma nova guia')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),

                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Imagem')
                    ->rounded(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),

                Tables\Columns\BooleanColumn::make('active')
                    ->label('Ativo')
                    ->trueIcon('heroicon-o-badge-check')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\BooleanColumn::make('fixed')
                    ->label('Fixo')
                    ->trueIcon('heroicon-o-badge-check')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicado')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                Filters\SelectFilter::make('active')
                    ->label('Ativos')
                    ->placeholder('Todos')
                    ->options([
                        '0' => 'Desativados',
                        '1' => 'Ativos',
                    ]),

                Filters\SelectFilter::make('fixed')
                    ->label('Fixados')
                    ->placeholder('Todos')
                    ->options([
                        '0' => 'No fijados',
                        '1' => 'Fixados',
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSlides::route('/'),
            'create' => Pages\CreateSlide::route('/create'),
            'view' => Pages\ViewSlide::route('/{record}'),
            'edit' => Pages\EditSlide::route('/{record}/edit'),
        ];
    }
}
