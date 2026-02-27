<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->unsignedBigInteger('habboassets_badge_id')->nullable()->after('code');
            $table->string('habboassets_hotel', 8)->nullable()->after('habboassets_badge_id');
            $table->timestamp('habboassets_source_created_at')->nullable()->after('habboassets_hotel');
            $table->timestamp('habboassets_source_updated_at')->nullable()->after('habboassets_source_created_at');
            $table->timestamp('imported_from_habboassets_at')->nullable()->after('habboassets_source_updated_at');

            $table->unique('habboassets_badge_id');
            $table->index('habboassets_hotel');
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropIndex(['habboassets_hotel']);
            $table->dropUnique(['habboassets_badge_id']);
            $table->dropColumn([
                'habboassets_badge_id',
                'habboassets_hotel',
                'habboassets_source_created_at',
                'habboassets_source_updated_at',
                'imported_from_habboassets_at',
            ]);
        });
    }
};
