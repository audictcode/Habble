<?php

namespace App\Providers;

use App\Models\{
    Topic,
    Article
};
use App\Observers\{
    TopicObserver,
    TopicCommentObserver,
    ArticleObserver
};
use Filament\Facades\Filament;
use App\Models\Topic\TopicComment;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('database.default') === 'sqlite') {
            try {
                DB::statement('PRAGMA journal_mode=WAL;');
                DB::statement('PRAGMA synchronous=NORMAL;');
                DB::statement('PRAGMA busy_timeout=10000;');
                DB::statement('PRAGMA temp_store=MEMORY;');
            } catch (\Throwable $exception) {
                // Evita romper el arranque si PRAGMA no se puede aplicar.
            }
        }

        Paginator::useBootstrap();
        
        Article::observe(ArticleObserver::class);
        Topic::observe(TopicObserver::class);
        TopicComment::observe(TopicCommentObserver::class);

        Filament::registerNavigationGroups([
            'Panel',
            'Academy',
            'Noticias',
            'Usuarios',
            'Foro',
            'Valores'
        ]);
    }
}
