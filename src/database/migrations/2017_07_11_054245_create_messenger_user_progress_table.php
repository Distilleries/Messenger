<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMessengerUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		
		Schema::create('messenger_user_progress', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->string('messenger_user_id');
			$table->string('messenger_config_id');
			$table->foreign('messenger_user_id')->references('id')->on('messenger_user')->onDelete('cascade');
			$table->foreign('messenger_config_id')->references('id')->on('messenger_config')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('messenger_user_progress');
	}

}
