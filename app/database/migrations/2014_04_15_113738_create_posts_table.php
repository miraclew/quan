<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('posts', function($table)
		{
		    $table->increments('id');
		    $table->integer('circle_id');
		    $table->integer('topic_id');
		    $table->integer('user_id');
		    $table->string('text');
		    $table->string('images')->nullable();
			$table->integer('comments_count')->default(0);
			$table->integer('likes_count')->default(0);

		    $table->index('circle_id');
		    $table->index('topic_id');
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
		Schema::dropIfExists('posts');
	}

}
