<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('messages', function($table)
		{
		    $table->increments('id');
		    $table->integer('from_id');
		    $table->integer('to_id');
		    $table->integer('to_type'); // user_id, channel_id
		    $table->integer('type'); // 消息场景 chat message, user action, system action
		    $table->integer('subtype');
		    $table->text('content');
		    $table->smallInteger('status'); // 应答状态 0: 未应答 1: 应答1, 2: 应答2 ...

		    $table->index('from_id');
		    $table->index('to_id');
		    $table->index('to_type');

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
		Schema::dropIfExists('messages');
	}

}
