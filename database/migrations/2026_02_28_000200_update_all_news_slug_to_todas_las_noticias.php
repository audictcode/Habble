<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sub_navigations')) {
            return;
        }

        DB::table('sub_navigations')
            ->whereRaw('lower(label) = ?', ['todas las noticias'])
            ->update(['slug' => '/pages/todas-las-noticias']);

        if (!Schema::hasTable('navigations')) {
            return;
        }

        DB::table('navigations')
            ->whereRaw('lower(label) in (?, ?)', ['habble', 'contenidos'])
            ->update(['slug' => '/pages/todas-las-noticias']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('sub_navigations')) {
            return;
        }

        DB::table('sub_navigations')
            ->whereRaw('lower(label) = ?', ['todas las noticias'])
            ->where('slug', '/pages/todas-las-noticias')
            ->update(['slug' => '/pages/noticias']);

        if (!Schema::hasTable('navigations')) {
            return;
        }

        DB::table('navigations')
            ->whereRaw('lower(label) in (?, ?)', ['habble', 'contenidos'])
            ->where('slug', '/pages/todas-las-noticias')
            ->update(['slug' => '/pages/noticias']);
    }
};
