<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_name')->unique()->nullable();
            $table->string('nick_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('tel')->unique();
            $table->string('api_token', 60);
            $table->string('verify_code', 10)->nullable();
            $table->timestamp('verify_code_expire_at')->nullable();
            $table->timestamp('verify_code_refresh_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
