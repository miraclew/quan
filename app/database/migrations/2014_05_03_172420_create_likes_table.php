<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLikesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('likes', function($table)
		{
		    $table->increments('id');
		    $table->integer('user_id');
		    $table->integer('type');
		    $table->integer('object_id');

		    $table->unique(array('user_id','type','object_id'));

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
		Schema::dropIfExists('likes');
	}

}
