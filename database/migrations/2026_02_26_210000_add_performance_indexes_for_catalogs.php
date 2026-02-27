<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('badges')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->index('published_at');
                $table->index('habbo_published_at');
            });
        }

        if (Schema::hasTable('furni_values')) {
            Schema::table('furni_values', function (Blueprint $table) {
                $table->index('category_id');
                $table->index('updated_at');
                $table->index(['category_id', 'updated_at']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('badges')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->dropIndex(['published_at']);
                $table->dropIndex(['habbo_published_at']);
            });
        }

        if (Schema::hasTable('furni_values')) {
            Schema::table('furni_values', function (Blueprint $table) {
                $table->dropIndex(['category_id']);
                $table->dropIndex(['updated_at']);
                $table->dropIndex(['category_id', 'updated_at']);
            });
        }
    }
};
