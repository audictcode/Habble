<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('campaign_infos', function (Blueprint $table) {
            $table->string('target_page')->default('informacion-campana')->after('slug');
            $table->text('excerpt')->nullable()->after('month_label');
            $table->string('banner_image_path')->nullable()->after('excerpt');
            $table->longText('body_html')->nullable()->after('banner_image_path');
            $table->json('info_cells')->nullable()->after('content_html');

            $table->string('primary_button_text')->nullable()->after('info_cells');
            $table->string('primary_button_url')->nullable()->after('primary_button_text');
            $table->string('secondary_button_text')->nullable()->after('primary_button_url');
            $table->string('secondary_button_url')->nullable()->after('secondary_button_text');
            $table->string('primary_button_color')->default('#0095ff')->after('secondary_button_url');
            $table->string('secondary_button_color')->default('#1f2937')->after('primary_button_color');

            $table->boolean('use_custom_html')->default(false)->after('secondary_button_color');
            $table->unsignedBigInteger('created_by_user_id')->nullable()->after('use_custom_html');
            $table->string('author_name')->nullable()->after('created_by_user_id');
            $table->string('author_avatar_url')->nullable()->after('author_name');

            $table->index('target_page');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_infos', function (Blueprint $table) {
            $table->dropIndex(['target_page']);
            $table->dropColumn([
                'target_page',
                'excerpt',
                'banner_image_path',
                'body_html',
                'info_cells',
                'primary_button_text',
                'primary_button_url',
                'secondary_button_text',
                'secondary_button_url',
                'primary_button_color',
                'secondary_button_color',
                'use_custom_html',
                'created_by_user_id',
                'author_name',
                'author_avatar_url',
            ]);
        });
    }
};
