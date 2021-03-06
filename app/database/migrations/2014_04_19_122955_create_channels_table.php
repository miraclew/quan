<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('channels', function($table)
		{
		    $table->increments('id');
		    $table->integer('type');
		    $table->string('title')->nullable();
		    $table->integer('creator_id');
		    $table->string('hash');
		    $table->integer('members_count');

		    $table->index('hash');

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
		Schema::dropIfExists('channels');
	}

}
