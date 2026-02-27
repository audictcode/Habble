<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHabboVerificationColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('habbo_name', 50)->nullable()->after('email');
            $table->string('habbo_hotel', 15)->nullable()->after('habbo_name');
            $table->string('habbo_verification_code', 20)->nullable()->after('habbo_hotel');
            $table->timestamp('habbo_verified_at')->nullable()->after('habbo_verification_code');

            $table->unique(['habbo_name', 'habbo_hotel'], 'users_habbo_name_hotel_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_habbo_name_hotel_unique');
            $table->dropColumn([
                'habbo_name',
                'habbo_hotel',
                'habbo_verification_code',
                'habbo_verified_at',
            ]);
        });
    }
}
