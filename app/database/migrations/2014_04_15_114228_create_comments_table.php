<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('comments', function($table)
		{
		    $table->increments('id');
		    $table->integer('circle_id');
		    $table->integer('post_id');
		    $table->integer('user_id');
		    $table->string('text');

		    $table->index('circle_id');
		    $table->index('post_id');
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
		Schema::dropIfExists('comments');
	}

}
