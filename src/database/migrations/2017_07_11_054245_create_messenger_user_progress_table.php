<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMessengerUserProgressTable extends Migration {

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
			$table->integer('messenger_user_id')->unsigned()->index();
			$table->foreign('messenger_user_id')->references('id')->on('messenger_users')->onDelete('cascade');
			$table->integer('messenger_config_id')->unsigned()->index();
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
