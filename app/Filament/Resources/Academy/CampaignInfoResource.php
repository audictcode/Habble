<?php

namespace App\Filament\Resources\Academy;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use App\Models\Academy\CampaignInfo;
use App\Models\Article\ArticleCategory;
use App\Filament\Resources\Academy\CampaignInfoResource\Pages;
use Illuminate\Support\Facades\Schema;

class CampaignInfoResource extends Resource
{
    protected static ?string $model = CampaignInfo::class;

    protected static ?string $slug = 'academy/campaign-info';

    protected static ?string $navigationGroup = 'Academy';

    protected static ?string $navigationLabel = 'Información campaña mensual';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        $hasCategoryColumn = Schema::hasColumn('campaign_infos', 'category_id');

        return $form
            ->schema([
                Grid::make(2)->schema([
                    Forms\Components\BelongsToSelect::make('category_id')
                        ->label('Categoría')
                        ->relationship('category', 'name')
                        ->options(ArticleCategory::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required($hasCategoryColumn)
                        ->helperText('Gestiona categorías en "Noticias > Gestionar Categorias". Para la ubicación automática usa "Noticias campaña" o "Información campaña".')
                        ->visible($hasCategoryColumn)
                        ->dehydrated($hasCategoryColumn)
                        ->columnSpan(2),

                    Forms\Components\Select::make('target_page')
                        ->label('Publicar en')
                        ->options([
                            'informacion-campana' => 'Información campaña',
                            'noticias-campana' => 'Noticias campaña',
                        ])
                        ->default('noticias-campana')
                        ->required(!$hasCategoryColumn)
                        ->visible(!$hasCategoryColumn),

                    Forms\Components\TextInput::make('month_label')
                        ->label('Mes')
                        ->placeholder('Ejemplo: Marzo 2026')
                        ->maxLength(255)
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('title')
                        ->label('Título')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('banner_image_path')
                        ->label('Banner por URL')
                        ->placeholder('https://dominio.com/banner.png')
                        ->helperText('Usa una URL completa como en Añadir noticia.')
                        ->columnSpan(2),

                    Forms\Components\RichEditor::make('body_html')
                        ->label('Contenido de la noticia')
                        ->fileAttachmentsDirectory('articles')
                        ->placeholder('Escribe aquí como una noticia. Puedes usar {usuario}.')
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('primary_button_text')
                        ->label('Botón principal')
                        ->placeholder('Iniciar sesión')
                        ->hidden(fn (callable $get) => self::isInfoCampaignCategory($get('category_id'))),
                    Forms\Components\TextInput::make('primary_button_url')
                        ->label('URL botón principal')
                        ->placeholder('/login')
                        ->hidden(fn (callable $get) => self::isInfoCampaignCategory($get('category_id'))),

                    Forms\Components\TextInput::make('secondary_button_text')
                        ->label('Botón secundario')
                        ->placeholder('Registrarse')
                        ->hidden(fn (callable $get) => self::isInfoCampaignCategory($get('category_id'))),
                    Forms\Components\TextInput::make('secondary_button_url')
                        ->label('URL botón secundario')
                        ->placeholder('/register')
                        ->hidden(fn (callable $get) => self::isInfoCampaignCategory($get('category_id'))),

                    Forms\Components\ColorPicker::make('primary_button_color')
                        ->label('Color botón principal')
                        ->default('#0095ff')
                        ->hidden(fn (callable $get) => self::isInfoCampaignCategory($get('category_id'))),
                    Forms\Components\ColorPicker::make('secondary_button_color')
                        ->label('Color botón secundario')
                        ->default('#1f2937')
                        ->hidden(fn (callable $get) => self::isInfoCampaignCategory($get('category_id'))),

                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Fecha de publicación'),

                    Forms\Components\Toggle::make('active')
                        ->label('Activo')
                        ->default(true),

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
                    ->label('Título')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->formatStateUsing(function ($state, CampaignInfo $record) {
                        if (filled($state)) {
                            return (string) $state;
                        }

                        return $record->target_page === 'noticias-campana'
                            ? 'Noticias campaña'
                            : 'Información campaña';
                    }),

                Tables\Columns\TextColumn::make('month_label')
                    ->label('Mes')
                    ->searchable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\BooleanColumn::make('active')
                    ->label('Activo'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaignInfos::route('/'),
            'create' => Pages\CreateCampaignInfo::route('/create'),
            'edit' => Pages\EditCampaignInfo::route('/{record}/edit'),
        ];
    }

    private static function isInfoCampaignCategory($categoryId): bool
    {
        $category = ArticleCategory::query()->find((int) $categoryId);
        if (!$category) {
            return false;
        }

        $normalizedName = \Illuminate\Support\Str::slug((string) $category->name);

        return in_array($normalizedName, ['informacion-campana', 'informacion-campana-mensual', 'info-campana'], true);
    }
}
