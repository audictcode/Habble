<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('furni_categories')) {
            return;
        }

        $categories = [
            ['name' => 'Rares', 'icon' => 'fa-gem'],
            ['name' => 'Furnis normales', 'icon' => 'fa-couch'],
            ['name' => 'Ropa', 'icon' => 'fa-shirt'],
            ['name' => 'Animales', 'icon' => 'fa-paw'],
            ['name' => 'Efectos', 'icon' => 'fa-wand-magic-sparkles'],
            ['name' => 'Sonidos', 'icon' => 'fa-music'],
        ];

        foreach ($categories as $category) {
            $existing = DB::table('furni_categories')
                ->whereRaw('lower(name) = ?', [strtolower($category['name'])])
                ->first();

            if ($existing) {
                DB::table('furni_categories')
                    ->where('id', $existing->id)
                    ->update(['icon' => $category['icon']]);
                continue;
            }

            DB::table('furni_categories')->insert([
                'name' => $category['name'],
                'icon' => $category['icon'],
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('furni_categories')) {
            return;
        }

        DB::table('furni_categories')
            ->whereIn('name', ['Rares', 'Furnis normales', 'Ropa', 'Animales', 'Efectos', 'Sonidos'])
            ->delete();
    }
};
