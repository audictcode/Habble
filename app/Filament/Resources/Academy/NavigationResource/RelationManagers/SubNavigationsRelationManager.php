<?php

namespace App\Filament\Resources\Academy\NavigationResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Forms\Components\Grid;
use Filament\Resources\RelationManagers\HasManyRelationManager;

class SubNavigationsRelationManager extends HasManyRelationManager
{
    protected static string $relationship = 'subNavigations';

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    Forms\Components\TextInput::make('label')
                        ->label('Label do Menu')
                        ->required(),
                ]),

                Forms\Components\TextInput::make('slug')
                    ->label('URL de redireccionamiento')
                    ->hint('<strong>Predeterminado:</strong> No tendrá redireccionamiento')
                    ->helperText('Acepta URL completa o ruta local (/pages/ejemplo).'),

                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->hint('<strong>Predeterminado:</strong> 0 - Ficará entre os primeiros (em ordem alfabética)')
                    ->label('Ordem de exibição (0 a 6)'),

                Forms\Components\Toggle::make('new_tab')
                    ->hint('<strong>Predeterminado:</strong> No abrirá em uma nova guia')
                    ->label('Abrir URL em uma nova guia'),

                Forms\Components\Select::make('min_rank')
                    ->label('Rango mínimo')
                    ->options(User::rankOptions(2, 7))
                    ->placeholder('Sin restricción')
                    ->helperText('Para radio, limita acceso a staff (DJ a Founder).'),

                Forms\Components\Toggle::make('visible')
                    ->hint('<strong>Predeterminado:</strong> Visível')
                    ->label('Visível no site'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->label('Label')
                    ->limit(15),

                Tables\Columns\TextColumn::make('order')
                    ->searchable()
                    ->label('Ordem de Exibição'),

                Tables\Columns\TextColumn::make('min_rank')
                    ->label('Rango mínimo')
                    ->formatStateUsing(fn ($state) => blank($state) ? 'Sin restricción' : (User::RANK_LABELS[(int) $state] ?? ('Rango ' . (int) $state))),

                Tables\Columns\BooleanColumn::make('visible')
                    ->label('Visível')
                    ->trueIcon('heroicon-o-badge-check')
                    ->falseIcon('heroicon-o-x-circle')
            ])
            ->filters([
                //
            ]);
    }
}
