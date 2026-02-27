<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileAndWalletColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('name');

            $table->unsignedInteger('astros')->default(0)->after('birth_date');
            $table->unsignedInteger('stelas')->default(0)->after('astros');
            $table->unsignedInteger('lunaris')->default(0)->after('stelas');
            $table->unsignedInteger('cosmos')->default(0)->after('lunaris');
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
            $table->dropColumn([
                'birth_date',
                'astros',
                'stelas',
                'lunaris',
                'cosmos',
            ]);
        });
    }
}
