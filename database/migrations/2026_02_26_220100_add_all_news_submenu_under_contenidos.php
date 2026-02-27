<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('navigations') || !Schema::hasTable('sub_navigations')) {
            return;
        }

        $navigation = DB::table('navigations')
            ->whereRaw('lower(label) = ?', ['contenidos'])
            ->first();

        if (!$navigation) {
            return;
        }

        $exists = DB::table('sub_navigations')
            ->where('navigation_id', $navigation->id)
            ->whereRaw('lower(label) = ?', ['todas las noticias'])
            ->first();

        if ($exists) {
            return;
        }

        $lastOrder = (int) (DB::table('sub_navigations')
            ->where('navigation_id', $navigation->id)
            ->max('order') ?? 0);

        DB::table('sub_navigations')->insert([
            'navigation_id' => $navigation->id,
            'label' => 'Todas las noticias',
            'slug' => '/pages/noticias',
            'new_tab' => false,
            'min_rank' => null,
            'order' => $lastOrder + 1,
            'visible' => true,
        ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('navigations') || !Schema::hasTable('sub_navigations')) {
            return;
        }

        $navigation = DB::table('navigations')
            ->whereRaw('lower(label) = ?', ['contenidos'])
            ->first();

        if (!$navigation) {
            return;
        }

        DB::table('sub_navigations')
            ->where('navigation_id', $navigation->id)
            ->whereRaw('lower(label) = ?', ['todas las noticias'])
            ->delete();
    }
};
