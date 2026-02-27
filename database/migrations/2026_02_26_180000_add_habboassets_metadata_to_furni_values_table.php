<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('furni_values', function (Blueprint $table) {
            $table->unsignedBigInteger('habboassets_furni_id')->nullable()->after('name');
            $table->string('habboassets_hotel', 16)->nullable()->after('habboassets_furni_id');
            $table->text('habboassets_source_url')->nullable()->after('habboassets_hotel');
            $table->timestamp('imported_from_habboassets_at')->nullable()->after('habboassets_source_url');

            $table->index('habboassets_hotel');
            $table->index('habboassets_furni_id');
        });
    }

    public function down(): void
    {
        Schema::table('furni_values', function (Blueprint $table) {
            $table->dropIndex(['habboassets_hotel']);
            $table->dropIndex(['habboassets_furni_id']);
            $table->dropColumn([
                'habboassets_furni_id',
                'habboassets_hotel',
                'habboassets_source_url',
                'imported_from_habboassets_at',
            ]);
        });
    }
};
