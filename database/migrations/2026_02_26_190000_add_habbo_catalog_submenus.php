<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('navigations') || !Schema::hasTable('sub_navigations')) {
            return;
        }

        $habboNavigation = DB::table('navigations')
            ->whereRaw('lower(label) = ?', ['habbo'])
            ->first();

        if (!$habboNavigation) {
            return;
        }

        $submenus = [
            ['label' => 'Todos los Furnis', 'slug' => '/pages/todos-los-furnis', 'order' => 2],
            ['label' => 'Toda la Ropa', 'slug' => '/pages/toda-la-ropa', 'order' => 3],
            ['label' => 'Todos los Rares', 'slug' => '/pages/todos-los-rares', 'order' => 4],
            ['label' => 'Todos los Sonidos', 'slug' => '/pages/todos-los-sonidos', 'order' => 5],
            ['label' => 'Todos los Animales', 'slug' => '/pages/todos-los-animales', 'order' => 6],
            ['label' => 'Todos los Efectos', 'slug' => '/pages/todos-los-efectos', 'order' => 7],
        ];

        foreach ($submenus as $submenu) {
            $existing = DB::table('sub_navigations')
                ->where('navigation_id', $habboNavigation->id)
                ->whereRaw('lower(label) = ?', [strtolower($submenu['label'])])
                ->first();

            if ($existing) {
                DB::table('sub_navigations')
                    ->where('id', $existing->id)
                    ->update([
                        'slug' => $submenu['slug'],
                        'order' => $submenu['order'],
                        'visible' => true,
                    ]);
                continue;
            }

            DB::table('sub_navigations')->insert([
                'navigation_id' => $habboNavigation->id,
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
        if (!Schema::hasTable('sub_navigations')) {
            return;
        }

        DB::table('sub_navigations')
            ->whereIn('slug', [
                '/pages/todos-los-furnis',
                '/pages/toda-la-ropa',
                '/pages/todos-los-rares',
                '/pages/todos-los-sonidos',
                '/pages/todos-los-animales',
                '/pages/todos-los-efectos',
            ])
            ->delete();
    }
};
