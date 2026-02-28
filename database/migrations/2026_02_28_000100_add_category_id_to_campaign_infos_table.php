<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('campaign_infos')) {
            return;
        }

        $hasArticlesCategories = Schema::hasTable('articles_categories');

        if (!Schema::hasColumn('campaign_infos', 'category_id')) {
            Schema::table('campaign_infos', function (Blueprint $table) use ($hasArticlesCategories) {
                $table->unsignedBigInteger('category_id')->nullable()->after('target_page');
                $table->index('category_id');

                if ($hasArticlesCategories) {
                    $table->foreign('category_id')
                        ->references('id')
                        ->on('articles_categories')
                        ->nullOnDelete();
                }
            });
        }

        if (!$hasArticlesCategories) {
            return;
        }

        $newsCategoryId = DB::table('articles_categories')
            ->whereRaw('lower(name) in (?, ?, ?)', ['noticias campaña', 'noticias campana', 'noticias-campana'])
            ->value('id');

        if (!$newsCategoryId) {
            $newsCategoryId = DB::table('articles_categories')->insertGetId([
                'name' => 'Noticias campaña',
                'icon' => null,
            ]);
        }

        $infoCategoryId = DB::table('articles_categories')
            ->whereRaw('lower(name) in (?, ?, ?, ?)', ['información campaña', 'informacion campaña', 'informacion-campana', 'información-campana'])
            ->value('id');

        if (!$infoCategoryId) {
            $infoCategoryId = DB::table('articles_categories')->insertGetId([
                'name' => 'Información campaña',
                'icon' => null,
            ]);
        }

        DB::table('campaign_infos')
            ->whereNull('category_id')
            ->where('target_page', 'noticias-campana')
            ->update(['category_id' => $newsCategoryId]);

        DB::table('campaign_infos')
            ->whereNull('category_id')
            ->where('target_page', 'informacion-campana')
            ->update(['category_id' => $infoCategoryId]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('campaign_infos') || !Schema::hasColumn('campaign_infos', 'category_id')) {
            return;
        }

        Schema::table('campaign_infos', function (Blueprint $table) {
            try {
                $table->dropForeign(['category_id']);
            } catch (\Throwable $exception) {
            }

            try {
                $table->dropIndex(['category_id']);
            } catch (\Throwable $exception) {
            }

            $table->dropColumn('category_id');
        });
    }
};
