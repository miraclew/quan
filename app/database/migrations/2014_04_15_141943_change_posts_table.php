<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePostsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('posts', function(Blueprint $table)
		{
			$table->dropColumn('title');
			$table->string('images')->nullable()->after('text');
			$table->integer('comments_count')->default(0)->after('images');
			$table->integer('likes_count')->default(0)->after('images');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('posts', function(Blueprint $table)
		{
			$table->string('title')->nullable()->after('user_id');
			$table->dropColumn('comments_count');
			$table->dropColumn('likes_count');
		});
	}

}
