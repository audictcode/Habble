<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sub_navigations')) {
            return;
        }

        if (!Schema::hasColumn('sub_navigations', 'min_rank')) {
            Schema::table('sub_navigations', function (Blueprint $table) {
                $table->unsignedTinyInteger('min_rank')->nullable()->after('new_tab');
            });
        }

        $radioNavigation = DB::table('navigations')
            ->whereRaw('lower(label) = ?', ['radio'])
            ->first();

        if (!$radioNavigation) {
            return;
        }

        DB::table('sub_navigations')
            ->where('navigation_id', $radioNavigation->id)
            ->whereNull('min_rank')
            ->update(['min_rank' => 2]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('sub_navigations') || !Schema::hasColumn('sub_navigations', 'min_rank')) {
            return;
        }

        Schema::table('sub_navigations', function (Blueprint $table) {
            $table->dropColumn('min_rank');
        });
    }
};
