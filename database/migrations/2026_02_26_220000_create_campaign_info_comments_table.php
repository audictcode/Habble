<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('campaign_info_comments')) {
            return;
        }

        Schema::create('campaign_info_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_info_id')->constrained('campaign_infos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_info_comments');
    }
};
