<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_games', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('game_url')->nullable();
            $table->unsignedInteger('xp_reward')->default(0);
            $table->unsignedInteger('astros_reward')->default(0);
            $table->unsignedInteger('stelas_reward')->default(0);
            $table->unsignedInteger('lunaris_reward')->default(0);
            $table->unsignedInteger('cosmos_reward')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_games');
    }
}

