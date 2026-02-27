<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyMissionsTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('daily_missions')) {
            Schema::create('daily_missions', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->text('intro_text')->nullable();
                $table->unsignedInteger('xp_reward')->default(0);
                $table->unsignedInteger('astros_reward')->default(0);
                $table->unsignedInteger('stelas_reward')->default(0);
                $table->unsignedInteger('lunaris_reward')->default(0);
                $table->unsignedInteger('cosmos_reward')->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_daily_mission_rewards')) {
            Schema::create('user_daily_mission_rewards', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('daily_mission_id')->constrained('daily_missions')->onDelete('cascade');
                $table->date('mission_date');
                $table->timestamp('rewarded_at')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'daily_mission_id', 'mission_date'], 'user_daily_mission_unique');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('user_daily_mission_rewards');
        Schema::dropIfExists('daily_missions');
    }
}

