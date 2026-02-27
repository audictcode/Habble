<?php

namespace App\Filament\Resources\Academy;

use Filament\Forms;
use Filament\Tables;
use App\Models\Badge;
use Filament\Tables\Filters;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Traits\ShowLatestResources;
use App\Filament\Resources\Academy\BadgeResource\Pages;

class BadgeResource extends Resource
{
    use ShowLatestResources;

    protected static ?string $model = Badge::class;

    protected static ?string $slug = 'academy/badges';

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $navigationGroup = 'Academy';

    protected static ?string $navigationLabel = 'Gestionar Emblemas';

    protected static ?string $navigationIcon = 'heroicon-o-view-grid';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Título')
                        ->required(),

                    Forms\Components\TextInput::make('description')
                        ->hint('<strong>Predeterminado:</strong> Sem descripción')
                        ->label('Descrição'),
                ]),

                Forms\Components\TextInput::make('code')
                    ->label('Código')
                    ->minLength(0)
                    ->maxLength(10)
                    ->required()
                    ->unique(Badge::class, 'code', fn ($record) => $record)
                    ->helperText('Entre 0 e 10 caracteres'),

                Forms\Components\TextInput::make('image_path')
                    ->label('URL da Imagem')
                    ->helperText('Opcional: si lo dejas vacío, HK usará automáticamente habboassets.com con el código de placa.')
                    ->nullable(),

                Forms\Components\Select::make('rarity')
                    ->placeholder('Escolha uma raridade')
                    ->required()
                    ->options(Badge::$rarities),

                Forms\Components\TextInput::make('content_slug')
                    ->url()
                    ->hint('<strong>Predeterminado:</strong> Sem conteúdo relacionado')
                    ->helperText('Coloque apenas URLs internos (do site)')
                    ->label('URL relacionado ao Emblema'),

                Forms\Components\DateTimePicker::make('habbo_published_at')
                    ->label('Fecha publicación en Habbo')
                    ->helperText('Fecha exacta de publicación oficial de la placa en el hotel de Habbo.'),

                Forms\Components\TextInput::make('habboassets_badge_id')
                    ->label('ID HabboAssets')
                    ->numeric()
                    ->helperText('Identificador original del badge en HabboAssets API.'),

                Forms\Components\TextInput::make('habboassets_hotel')
                    ->label('Found (hotel)')
                    ->helperText('Hotel de origen reportado por HabboAssets (es, com, com.br, etc).'),

                Forms\Components\DateTimePicker::make('habboassets_source_created_at')
                    ->label('Publicado en HabboAssets'),

                Forms\Components\DateTimePicker::make('imported_from_habboassets_at')
                    ->label('Transferido a la web'),

                Forms\Components\DateTimePicker::make('published_at')
                    ->label('Fecha de publicación en la web')
                    ->helperText('Cuándo debe mostrarse esta placa en la web.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query
                    ->orderByRaw("CASE WHEN habboassets_badge_id IS NULL THEN 1 ELSE 0 END ASC")
                    ->orderByDesc('habboassets_badge_id')
                    ->orderByRaw("CASE WHEN coalesce(habboassets_source_created_at, habbo_published_at) IS NULL THEN 1 ELSE 0 END ASC")
                    ->orderByRaw("coalesce(habboassets_source_created_at, habbo_published_at) DESC")
                    ->orderByDesc('id');
            })
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),

                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Imagem')
                    ->size(30),

                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->label('Ordem de Exibição'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->label('Título')
                    ->limit(15),

                Tables\Columns\TextColumn::make('rarity')
                    ->enum(Badge::$rarities),

                Tables\Columns\TextColumn::make('habbo_published_at')
                    ->label('Publicado en Habbo')
                    ->dateTime('d/m/Y H:i'),

                Tables\Columns\TextColumn::make('imported_on_hotel')
                    ->label('Importado en Hotel')
                    ->getStateUsing(function (Badge $record): string {
                        $hotel = trim((string) ($record->habboassets_hotel ?? ''));
                        $publishedAt = $record->habboassets_source_created_at ?: $record->habbo_published_at;

                        $dateLabel = $publishedAt ? $publishedAt->format('d/m/Y H:i') : 'Sin fecha';
                        $hotelLabel = $hotel !== '' ? strtoupper($hotel) : 'SIN HOTEL';

                        return $dateLabel . ' | ' . $hotelLabel;
                    }),

                Tables\Columns\TextColumn::make('habboassets_badge_id')
                    ->label('ID HabboAssets'),

                Tables\Columns\TextColumn::make('habboassets_hotel')
                    ->label('Found'),

                Tables\Columns\TextColumn::make('imported_from_habboassets_at')
                    ->label('Transferido')
                    ->dateTime('d/m/Y H:i'),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicado en web')
                    ->dateTime('d/m/Y H:i'),

                Tables\Columns\TextColumn::make('users_count')
                    ->extraAttributes(['class' => 'font-bold'])
                    ->label('Usuarios que possuem')
                    ->counts('users')
            ])
            ->filters([
                Filters\SelectFilter::make('rarity')
                    ->label('Raridade')
                    ->placeholder('Todas')
                    ->options(Badge::$rarities),

                Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Criado a partir de'),
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
            BadgeResource\RelationManagers\UsersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBadges::route('/'),
            'create' => Pages\CreateBadge::route('/create'),
            'edit' => Pages\EditBadge::route('/{record}/edit'),
        ];
    }
}
