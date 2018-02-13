<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServerModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server_modules', function (Blueprint $table) {
            $table->increments('id');
	        $table->smallInteger('server_id')->unsigned();
	        $table->smallInteger('module_id')->unsigned();
	        $table->string('status');
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
        Schema::dropIfExists('server_modules');
    }
}
