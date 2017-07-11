<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMessengerUserVariablesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		
		Schema::create('messenger_user_variables', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('variable');
			$table->string('value');
			$table->integer('messenger_user_id')->unsigned()->index();
			$table->foreign('messenger_user_id')->references('id')->on('messenger_users')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('messenger_user_variables');
	}

}
