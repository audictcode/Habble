<?php

namespace App\Filament\Resources\Academy;

use Filament\Forms;
use Filament\Tables;
use App\Models\DailyMission;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\Academy\DailyMissionResource\Pages;

class DailyMissionResource extends Resource
{
    protected static ?string $model = DailyMission::class;

    protected static ?string $slug = 'academy/daily-missions';

    protected static ?string $navigationGroup = 'Usuarios';

    protected static ?string $navigationLabel = 'Misiones Diarias';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-check';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->rows(3),

                Forms\Components\Textarea::make('intro_text')
                    ->label('Texto de misión')
                    ->rows(3),

                Forms\Components\DateTimePicker::make('published_at')
                    ->label('Fecha de publicación'),

                Forms\Components\Grid::make(3)->schema([
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
                ]),

                Forms\Components\Toggle::make('active')
                    ->label('Activa')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
            Tables\Columns\TextColumn::make('title')->label('Misión')->searchable(),
            Tables\Columns\TextColumn::make('xp_reward')->label('XP'),
            Tables\Columns\TextColumn::make('astros_reward')->label('Astros'),
            Tables\Columns\TextColumn::make('stelas_reward')->label('Auroras'),
            Tables\Columns\TextColumn::make('published_at')->label('Publicado')->dateTime('d/m/Y H:i'),
            Tables\Columns\BooleanColumn::make('active')->label('Activa'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyMissions::route('/'),
            'create' => Pages\CreateDailyMission::route('/create'),
            'edit' => Pages\EditDailyMission::route('/{record}/edit'),
        ];
    }
}
