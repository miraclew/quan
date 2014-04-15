<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTokensTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tokens', function($table)
		{
		    $table->increments('id');
		    $table->string('token')->unique();
		    $table->timestamp('expires_at');
		    $table->boolean('is_locked');
		    $table->integer('user_id');
		    $table->string('refresh_token');

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
		Schema::dropIfExists('tokens');
	}

}
