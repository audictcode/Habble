<?php

namespace App\Console\Commands\Academy;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class FullSyncBadges extends Command
{
    protected $signature = 'badges:full-sync
        {--skip-import : Solo ejecutar reparación}
        {--limit=2000 : Límite por página para import}
        {--max-pages=0 : Máximo de páginas para import (0 sin límite)}';

    protected $description = 'Importa todos los badges desde HabboAssets y repara metadata';

    public function handle(): int
    {
        $this->info('Iniciando sincronización completa de badges...');

        if (!(bool) $this->option('skip-import')) {
            $importArgs = [
                '--all' => true,
                '--limit' => (int) $this->option('limit'),
            ];

            $maxPages = (int) $this->option('max-pages');
            if ($maxPages > 0) {
                $importArgs['--max-pages'] = $maxPages;
            }

            try {
                $importCode = Artisan::call('badges:import-habboassets', $importArgs);
                $this->line(trim(Artisan::output()));
            } catch (\Throwable $exception) {
                $importCode = self::FAILURE;
                $this->error('Error en importación: ' . $exception->getMessage());
            }

            if ($importCode !== self::SUCCESS) {
                $this->warn('Import falló o parcial. Continuando con reparación local de metadata...');
            }
        }

        try {
            Artisan::call('badges:repair-metadata', ['--set-published' => true]);
            $this->line(trim(Artisan::output()));
        } catch (\Throwable $exception) {
            $this->error('Error reparando metadata: ' . $exception->getMessage());
            return self::FAILURE;
        }

        $this->info('Sincronización completa finalizada.');
        return self::SUCCESS;
    }
}
