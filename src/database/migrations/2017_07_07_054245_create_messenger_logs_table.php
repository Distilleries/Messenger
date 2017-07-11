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
		
		Schema::create('messenger_logs', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->string('messenger_user_id');
			$table->string('text');
			$table->string('intent');
			$table->text('response');
			$table->dateTime('inserted_at');
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
		Schema::drop('messenger_logs');
	}

}
