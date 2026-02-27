<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as FilamentDashboard;

class Dashboard extends FilamentDashboard
{
    protected static ?string $navigationGroup = 'Panel';

    protected static ?string $navigationLabel = 'Página inicial';
}