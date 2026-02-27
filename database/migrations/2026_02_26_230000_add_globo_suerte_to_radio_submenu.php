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

        $radioNavigation = DB::table('navigations')
            ->whereRaw('lower(label) = ?', ['radio'])
            ->first();

        if (!$radioNavigation) {
            return;
        }

        $label = 'Globo de la Suerte';
        $slug = 'https://www.habbo.es/room/125772597';

        $existing = DB::table('sub_navigations')
            ->where('navigation_id', $radioNavigation->id)
            ->whereRaw('lower(label) = ?', [strtolower($label)])
            ->first();

        if ($existing) {
            DB::table('sub_navigations')
                ->where('id', $existing->id)
                ->update([
                    'slug' => $slug,
                    'new_tab' => true,
                    'order' => 2,
                    'visible' => true,
                ]);

            return;
        }

        DB::table('sub_navigations')->insert([
            'navigation_id' => $radioNavigation->id,
            'label' => $label,
            'slug' => $slug,
            'new_tab' => true,
            'order' => 2,
            'visible' => true,
        ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('sub_navigations')) {
            return;
        }

        DB::table('sub_navigations')
            ->whereRaw('lower(label) = ?', ['globo de la suerte'])
            ->where('slug', 'https://www.habbo.es/room/125772597')
            ->delete();
    }
};
