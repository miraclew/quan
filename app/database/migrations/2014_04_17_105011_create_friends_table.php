<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFriendsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('friends', function($table)
		{
		    $table->increments('id');
		    $table->string('user_id');
		    $table->string('friend_id');
		    $table->smallInteger('status');

			$table->unique(array('user_id','friend_id'));
		    $table->index('user_id');
		    $table->index('friend_id');

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
		Schema::dropIfExists('friends');
	}

}
