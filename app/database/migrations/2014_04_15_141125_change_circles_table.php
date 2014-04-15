<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCirclesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('circles', function(Blueprint $table)
		{
			$table->string('lat')->nullable()->after('members_count');
			$table->string('lng')->nullable()->after('members_count');
			$table->float('radius')->nullable()->after('members_count');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('circles', function(Blueprint $table)
		{
			$table->dropColumn('lat');
			$table->dropColumn('lng');
			$table->dropColumn('radius');
		});
	}

}
