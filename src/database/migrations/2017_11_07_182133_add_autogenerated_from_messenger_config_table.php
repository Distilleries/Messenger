<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutogeneratedFromMessengerConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('messenger_config', function (Blueprint $table) {
            $table->boolean('autogenerated')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messenger_config', function (Blueprint $table) {
            $table->dropColumn('autogenerated');
        });
    }
}
