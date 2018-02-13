<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServerModuleOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server_module_options', function (Blueprint $table) {
            $table->increments('id');
	        $table->smallInteger('server_module_id')->unsigned();
	        $table->smallInteger('server_id')->unsigned();
	        $table->smallInteger('module_id')->unsigned();
	        $table->smallInteger('module_option_id')->unsigned();
	        $table->string('value');
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
        Schema::dropIfExists('server_module_options');
    }
}
