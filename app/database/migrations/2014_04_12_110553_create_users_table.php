<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function($table)
		{
		    $table->increments('id');
		    $table->string('username')->unique();
		    $table->string('email')->unique();
		    $table->string('nickname');
		    $table->string('password');
		    $table->smallInteger('gender');
		    $table->boolean('is_locked');
		    $table->string('open_id');
		    $table->string('avatar');
		    $table->timestamp('last_login');

		    $table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('users');
	}

}
