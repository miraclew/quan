<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopicsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('topics', function($table)
		{
		    $table->increments('id');
		    $table->integer('circle_id');
		    $table->integer('user_id');
		    $table->string('title');
		    $table->string('icon');
		    $table->integer('sort');

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
		Schema::dropIfExists('topics');
	}

}
