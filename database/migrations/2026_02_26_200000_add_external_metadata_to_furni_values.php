<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('furni_values', function (Blueprint $table) {
            $table->string('source_provider', 32)->nullable()->after('habboassets_source_url');
            $table->string('habbofurni_item_id')->nullable()->after('source_provider');
            $table->timestamp('habbofurni_imported_at')->nullable()->after('habbofurni_item_id');
            $table->json('external_metadata')->nullable()->after('habbofurni_imported_at');
        });
    }

    public function down(): void
    {
        Schema::table('furni_values', function (Blueprint $table) {
            $table->dropColumn([
                'source_provider',
                'habbofurni_item_id',
                'habbofurni_imported_at',
                'external_metadata',
            ]);
        });
    }
};
