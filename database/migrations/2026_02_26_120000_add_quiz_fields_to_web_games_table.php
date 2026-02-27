<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuizFieldsToWebGamesTable extends Migration
{
    public function up()
    {
        Schema::table('web_games', function (Blueprint $table) {
            if (!Schema::hasColumn('web_games', 'category')) {
                $table->string('category')->default('arcade')->after('game_url');
            }
            if (!Schema::hasColumn('web_games', 'game_type')) {
                $table->string('game_type')->default('external')->after('category');
            }
            if (!Schema::hasColumn('web_games', 'intro_text')) {
                $table->text('intro_text')->nullable()->after('game_type');
            }
            if (!Schema::hasColumn('web_games', 'info_text')) {
                $table->text('info_text')->nullable()->after('intro_text');
            }
            if (!Schema::hasColumn('web_games', 'option_title')) {
                $table->string('option_title')->nullable()->after('info_text');
            }
            if (!Schema::hasColumn('web_games', 'option_description')) {
                $table->text('option_description')->nullable()->after('option_title');
            }
            if (!Schema::hasColumn('web_games', 'option_reward_text')) {
                $table->string('option_reward_text')->nullable()->after('option_description');
            }
            if (!Schema::hasColumn('web_games', 'quiz_questions')) {
                $table->longText('quiz_questions')->nullable()->after('option_reward_text');
            }
        });
    }

    public function down()
    {
        Schema::table('web_games', function (Blueprint $table) {
            $columns = [
                'category',
                'game_type',
                'intro_text',
                'info_text',
                'option_title',
                'option_description',
                'option_reward_text',
                'quiz_questions',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('web_games', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}

