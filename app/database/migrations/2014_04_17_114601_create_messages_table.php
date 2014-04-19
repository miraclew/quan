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
		    $table->integer('channel_id');
		    $table->integer('type'); // 消息场景 chat message, user action, system action
		    $table->integer('sub_type');
		    $table->string('mime_type');
		    $table->text('content');
		    $table->smallInteger('ack_status'); // 应答状态 0: 未应答 1: 应答1, 2: 应答2 ...
		    $table->smallInteger('status');

		    $table->index('from_id');
		    $table->index('channel_id');

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
