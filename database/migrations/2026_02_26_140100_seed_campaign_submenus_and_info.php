<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('campaign_infos')) {
            $exists = DB::table('campaign_infos')
                ->where('slug', 'informacion-campana')
                ->exists();

            if (!$exists) {
                DB::table('campaign_infos')->insert([
                    'title' => 'Información campaña',
                    'slug' => 'informacion-campana',
                    'month_label' => 'Febrero 2026',
                    'content_html' => <<<'HTML'
<div class="wrapper content-border">
    <main id="main" class="content">
        <article>
            <section>
                <header class="post-header">
                    <h1 class="post-title">Febrero 2026 en Habbo</h1>
                </header>
                <div class="post-inner">
                    <div class="post-content">
                        <p>Contenido mensual de campaña.</p>
                        <p>Nota: Puedes pegar aquí el bloque HTML completo desde HK en "Información campaña mensual".</p>
                    </div>
                </div>
            </section>
        </article>
    </main>
</div>
HTML,
                    'active' => true,
                    'published_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (!Schema::hasTable('navigations') || !Schema::hasTable('sub_navigations')) {
            return;
        }

        $navigation = DB::table('navigations')
            ->whereRaw('lower(label) in (?, ?, ?)', ['contenidos', 'contents', 'conteudos'])
            ->orderBy('id')
            ->first();

        if (!$navigation) {
            $navigation = DB::table('navigations')->orderBy('id')->first();
        }

        if (!$navigation) {
            return;
        }

        $submenus = [
            [
                'label' => 'Noticias campaña',
                'slug' => '/pages/noticias-campana',
                'order' => 10,
            ],
            [
                'label' => 'Informacion campaña',
                'slug' => '/pages/informacion-campana',
                'order' => 11,
            ],
        ];

        foreach ($submenus as $submenu) {
            $exists = DB::table('sub_navigations')
                ->where('navigation_id', $navigation->id)
                ->whereRaw('lower(label) = ?', [strtolower($submenu['label'])])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('sub_navigations')->insert([
                'navigation_id' => $navigation->id,
                'label' => $submenu['label'],
                'slug' => $submenu['slug'],
                'new_tab' => false,
                'order' => $submenu['order'],
                'visible' => true,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sub_navigations')) {
            DB::table('sub_navigations')
                ->whereIn('slug', ['/pages/noticias-campana', '/pages/informacion-campana'])
                ->delete();
        }

        if (Schema::hasTable('campaign_infos')) {
            DB::table('campaign_infos')
                ->where('slug', 'informacion-campana')
                ->delete();
        }
    }
};
