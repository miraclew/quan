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
		    $table->string('address');
   			$table->string('lat')->nullable();
			$table->string('lng')->nullable();
			$table->float('radius')->nullable();
			$table->string('place_uid')->nullable()->unique();

		    $table->integer('posts_count')->default(0);
		    $table->integer('members_count')->default(0);

		    $table->integer('creator_id');
		    $table->boolean('is_locked');
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
