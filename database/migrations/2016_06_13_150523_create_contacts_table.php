<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('contact_user_id');
            $table->string('display_name', 128)->nullable();
            $table->enum('relation', ['star', 'follow', 'stranger', 'block']);
            $table->string('conv_id', 60);
            
            $table->timestamps();
            $table->primary(['user_id', 'contact_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('contacts');
    }
}
