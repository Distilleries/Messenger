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
		
		Schema::create('messenger_config', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->string('type');
			$table->string('content');
			$table->string('group_id')->nullable();
			$table->integer('parent_id')->nullable();
			$table->foreign('parent_id')->references('id')->on('messenger_config')->onDelete('cascade');
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
