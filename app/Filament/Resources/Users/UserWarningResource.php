<?php

namespace App\Filament\Resources\Users;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Tables\Filters;
use Filament\Resources\Table;
use App\Models\User\UserWarning;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Users\UserWarningResource\Pages;

class UserWarningResource extends Resource
{
    protected static ?string $model = UserWarning::class;

    protected static ?string $slug = 'users/warnings';

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationGroup = 'Usuarios';

    protected static ?string $navigationLabel = 'Gestionar Advertências';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    Forms\Components\BelongsToSelect::make('user_id')
                        ->label('Usuario')
                        ->relationship('user', 'username')
                        ->placeholder('Usuario')
                        ->disablePlaceholderSelection()
                        ->options(User::pluck('username', 'id'))
                        ->searchable()
                        ->required(),

                    Forms\Components\Textarea::make('reason')
                        ->hint('<strong>Predeterminado:</strong> Vazio')
                        ->label('Razão da Advertência'),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.username')
                    ->searchable()
                    ->label('Usuario')
                    ->limit(20),

                Tables\Columns\TextColumn::make('admin.username')
                    ->searchable()
                    ->label('Advertido por')
                    ->limit(20),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Advertido em')
                    ->date('d/m/y \à\s H:i'),
            ])
            ->filters([
                Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Criadas a partir de'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
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
            'index' => Pages\ListUserWarnings::route('/'),
            'create' => Pages\CreateUserWarning::route('/create'),
            'view' => Pages\ViewWarning::route('/{record}'),
            'edit' => Pages\EditUserWarning::route('/{record}/edit'),
        ];
    }
}
