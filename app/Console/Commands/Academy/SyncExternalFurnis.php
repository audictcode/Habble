<?php

namespace App\Console\Commands\Academy;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SyncExternalFurnis extends Command
{
    protected $signature = 'furnis:sync-external
        {--dry-run : Simular importaciones}
        {--max-pages=1 : Máximo de páginas por proveedor}';

    protected $description = 'Sincroniza furnis desde HabboAssets + Habbofurni con categorías y metadatos';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $maxPages = max(1, (int) $this->option('max-pages'));

        $this->info('Sincronizando furnis desde HabboAssets...');
        $habboAssetsArgs = [
            '--hotels' => 'es,com,com.br,de,fr,it,nl,fi,tr',
            '--max-pages' => $maxPages,
            '--category' => 'auto',
        ];
        if ($dryRun) {
            $habboAssetsArgs['--dry-run'] = true;
        }
        Artisan::call('furnis:import-habboassets', $habboAssetsArgs);
        $this->line(trim(Artisan::output()));

        $this->info('Sincronizando furnis desde Habbofurni...');
        $habbofurniArgs = [
            '--max-pages' => $maxPages,
            '--paths' => 'furni,rares,ropa,animales,efectos,sonidos',
        ];
        if ($dryRun) {
            $habbofurniArgs['--dry-run'] = true;
        }
        Artisan::call('furnis:import-habbofurni', $habbofurniArgs);
        $this->line(trim(Artisan::output()));

        $this->info('Sincronización externa de furnis finalizada.');
        return self::SUCCESS;
    }
}
