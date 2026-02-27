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

        $contenidos = DB::table('navigations')
            ->whereRaw('lower(label) = ?', ['contenidos'])
            ->first();

        if (!$contenidos) {
            return;
        }

        $submenus = [
            'Noticias campaña' => '/pages/noticias-campana',
            'Informacion campaña' => '/pages/informacion-campana',
        ];

        foreach ($submenus as $label => $slug) {
            $existing = DB::table('sub_navigations')
                ->whereRaw('lower(label) = ?', [strtolower($label)])
                ->first();

            if ($existing) {
                DB::table('sub_navigations')
                    ->where('id', $existing->id)
                    ->update([
                        'navigation_id' => $contenidos->id,
                        'slug' => $slug,
                        'visible' => true,
                    ]);
                continue;
            }

            DB::table('sub_navigations')->insert([
                'navigation_id' => $contenidos->id,
                'label' => $label,
                'slug' => $slug,
                'new_tab' => false,
                'order' => 10,
                'visible' => true,
            ]);
        }
    }

    public function down(): void
    {
        // Keep menu entries on rollback to avoid deleting user-managed navigation.
    }
};
