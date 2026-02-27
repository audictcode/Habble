<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPublicationDatesToHkContentTables extends Migration
{
    public function up()
    {
        Schema::table('web_games', function (Blueprint $table) {
            if (!Schema::hasColumn('web_games', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('quiz_questions');
            }
            if (!Schema::hasColumn('web_games', 'participation_ends_at')) {
                $table->timestamp('participation_ends_at')->nullable()->after('published_at');
            }
        });

        Schema::table('badges', function (Blueprint $table) {
            if (!Schema::hasColumn('badges', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('content_slug');
            }
            if (!Schema::hasColumn('badges', 'habbo_published_at')) {
                $table->timestamp('habbo_published_at')->nullable()->after('published_at');
            }
        });

        Schema::table('daily_missions', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_missions', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('intro_text');
            }
        });

        Schema::table('slides', function (Blueprint $table) {
            if (!Schema::hasColumn('slides', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('image_path');
            }
        });

        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('content');
            }
        });
    }

    public function down()
    {
        Schema::table('web_games', function (Blueprint $table) {
            if (Schema::hasColumn('web_games', 'participation_ends_at')) {
                $table->dropColumn('participation_ends_at');
            }
            if (Schema::hasColumn('web_games', 'published_at')) {
                $table->dropColumn('published_at');
            }
        });

        Schema::table('badges', function (Blueprint $table) {
            if (Schema::hasColumn('badges', 'published_at')) {
                $table->dropColumn('published_at');
            }
        });

        Schema::table('daily_missions', function (Blueprint $table) {
            if (Schema::hasColumn('daily_missions', 'published_at')) {
                $table->dropColumn('published_at');
            }
        });

        Schema::table('slides', function (Blueprint $table) {
            if (Schema::hasColumn('slides', 'published_at')) {
                $table->dropColumn('published_at');
            }
        });

        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'published_at')) {
                $table->dropColumn('published_at');
            }
        });
    }
}
