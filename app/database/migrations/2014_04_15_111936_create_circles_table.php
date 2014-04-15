<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCirclesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('circles', function($table)
		{
		    $table->increments('id');
		    $table->string('name');
		    $table->string('location');
		    $table->integer('creator_id');
		    $table->boolean('is_locked');
		    $table->integer('posts_count');
		    $table->integer('members_count');

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
		Schema::dropIfExists('circles');
	}

}
