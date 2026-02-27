<?php

namespace App\Filament\Resources\Academy;

use Filament\Forms;
use Filament\Tables;
use App\Models\WebGame;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use App\Filament\Resources\Academy\WebGameResource\Pages;

class WebGameResource extends Resource
{
    protected static ?string $model = WebGame::class;

    protected static ?string $slug = 'academy/web-games';

    protected static ?string $navigationGroup = 'Academy';

    protected static ?string $navigationLabel = 'Juegos Web';

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 2])->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Título')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(WebGame::class, 'slug', fn ($record) => $record),

                    Forms\Components\Textarea::make('description')
                        ->label('Descripción')
                        ->rows(3)
                        ->columnSpan(2),

                    Forms\Components\Select::make('category')
                        ->label('Categoría')
                        ->options(WebGame::CATEGORY_OPTIONS)
                        ->default('arcade')
                        ->required(),

                    Forms\Components\Select::make('game_type')
                        ->label('Tipo de juego')
                        ->options([
                            'external' => 'Externo',
                            'quiz' => 'Quiz',
                        ])
                        ->default('external')
                        ->required(),

                    Forms\Components\TextInput::make('thumbnail_url')
                        ->label('URL miniatura')
                        ->url()
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('game_url')
                        ->label('URL del juego')
                        ->url()
                        ->columnSpan(2),

                    Forms\Components\Textarea::make('intro_text')
                        ->label('Texto de introducción')
                        ->rows(3)
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('option_title')
                        ->label('Título de opciones iniciales')
                        ->columnSpan(2),

                    Forms\Components\Textarea::make('option_description')
                        ->label('Descripción de opciones iniciales')
                        ->rows(2)
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('option_reward_text')
                        ->label('Texto de recompensa inicial')
                        ->columnSpan(2),

                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Fecha de publicación')
                        ->columnSpan(1),

                    Forms\Components\DateTimePicker::make('participation_ends_at')
                        ->label('Fecha máxima de participación')
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('xp_reward')
                        ->label('XP')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),

                    Forms\Components\TextInput::make('astros_reward')
                        ->label('Astros')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),

                    Forms\Components\TextInput::make('stelas_reward')
                        ->label('Auroras')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),

                    Forms\Components\TextInput::make('lunaris_reward')
                        ->label('Solarix')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),

                    Forms\Components\TextInput::make('cosmos_reward')
                        ->label('Cosmos')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),

                    Forms\Components\Toggle::make('active')
                        ->label('Publicado')
                        ->default(true)
                        ->columnSpan(2),

                    Forms\Components\Repeater::make('quiz_questions')
                        ->label('Preguntas del Quiz (máximo 20)')
                        ->maxItems(20)
                        ->schema([
                            Forms\Components\TextInput::make('question')
                                ->label('Pregunta')
                                ->required(),
                            Forms\Components\TextInput::make('option_a')
                                ->label('Respuesta A')
                                ->required(),
                            Forms\Components\TextInput::make('option_b')
                                ->label('Respuesta B')
                                ->required(),
                            Forms\Components\TextInput::make('option_c')
                                ->label('Respuesta C')
                                ->required(),
                            Forms\Components\TextInput::make('option_d')
                                ->label('Respuesta D')
                                ->required(),
                            Forms\Components\Select::make('correct_option')
                                ->label('Respuesta correcta')
                                ->options([
                                    'a' => 'A',
                                    'b' => 'B',
                                    'c' => 'C',
                                    'd' => 'D',
                                ])
                                ->required(),
                            Forms\Components\Textarea::make('explanation')
                                ->label('Explicación final (opcional)')
                                ->rows(2),
                        ])
                        ->columnSpan(2),

                    Forms\Components\Textarea::make('info_text')
                        ->label('Celda de información final')
                        ->rows(3)
                        ->columnSpan(2),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Juego')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
                    ->formatStateUsing(fn ($state) => WebGame::CATEGORY_OPTIONS[$state] ?? $state),

                Tables\Columns\TextColumn::make('game_type')
                    ->label('Tipo'),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('participation_ends_at')
                    ->label('Fin participación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('xp_reward')
                    ->label('XP'),

                Tables\Columns\TextColumn::make('astros_reward')
                    ->label('Astros'),

                Tables\Columns\TextColumn::make('stelas_reward')
                    ->label('Auroras'),

                Tables\Columns\BooleanColumn::make('active')
                    ->label('Publicado'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebGames::route('/'),
            'create' => Pages\CreateWebGame::route('/create'),
            'edit' => Pages\EditWebGame::route('/{record}/edit'),
        ];
    }
}
