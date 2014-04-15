<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFollowsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('follows', function($table)
		{
		    $table->increments('id');
		    $table->integer('circle_id');
		    $table->integer('user_id');
		    $table->integer('type');

		    $table->unique(array('circle_id','user_id'));
		    $table->index('circle_id');
		    $table->index('user_id');

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
		Schema::dropIfExists('follows');
	}

}
