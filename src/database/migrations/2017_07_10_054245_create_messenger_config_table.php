<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMessengerConfigTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		Schema::create('messenger_config', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->string('type');
			$table->text('content')->nullable();
			$table->string('payload')->nullable();
			$table->string('group_id')->nullable();
			$table->integer('parent_id')->unsigned()->index()->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('messenger_config');
	}

}
