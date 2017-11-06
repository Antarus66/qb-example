<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQbTokensFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('qb_access_token')->nullable();
            $table->text('qb_refresh_token')->nullable();
            $table->dateTime('qb_refresh_token_updated_at')->nullable();
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
            $table->dropColumn('qb_access_token');
            $table->dropColumn('qb_refresh_token');
            $table->dropColumn('qb_refresh_token_updated_at');
        });
    }
}
