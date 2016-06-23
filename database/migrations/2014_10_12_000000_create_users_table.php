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
            $table->string('user_name', 128)->unique()->nullable();
            $table->string('display_name', 128)->nullable();
            $table->string('email', 128)->unique()->nullable();
            $table->string('tel', 32)->unique();
            $table->string('avatar_url', 1024)->nullable();
            $table->enum('type', ['person', 'bot']);
            $table->boolean('searchable')->default(true);
            $table->string('verify_code', 16)->nullable();
            $table->timestamp('verify_code_expire_at')->nullable();
            $table->timestamp('verify_code_refresh_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
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
