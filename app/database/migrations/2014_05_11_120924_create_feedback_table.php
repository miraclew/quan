<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbackTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('feedbacks', function($table)
		{
		    $table->increments('id');
		    $table->integer('user_id');
		    $table->integer('type')->default(1);
		    $table->string('text');
		    $table->integer('process_status')->default(0);
		    $table->integer('process_by')->default(0);

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
		Schema::dropIfExists('feedbacks');
	}

}
