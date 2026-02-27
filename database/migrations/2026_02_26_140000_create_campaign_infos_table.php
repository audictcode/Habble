<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaign_infos', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Información campaña');
            $table->string('slug')->default('informacion-campana')->index();
            $table->string('month_label')->nullable();
            $table->longText('content_html')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_infos');
    }
};
