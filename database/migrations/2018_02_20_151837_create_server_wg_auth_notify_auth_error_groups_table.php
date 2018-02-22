<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServerWgAuthNotifyAuthErrorGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server_wg_auth_notify_auth_error_groups', function (Blueprint $table) {
            $table->increments('id');
	        $table->unsignedInteger( 'server_id' );
	        $table->boolean( 'commander' )->default(0);
	        $table->boolean( 'executive_officer' )->default(0);
	        $table->boolean( 'personnel_officer' )->default(0);
	        $table->boolean( 'combat_officer' )->default(0);
	        $table->boolean( 'intelligence_officer' )->default(0);
	        $table->boolean( 'quartermaster' )->default(0);
	        $table->boolean( 'recruitment_officer' )->default(0);
	        $table->boolean( 'junior_officer' )->default(0);
	        $table->boolean( 'private' )->default(0);
	        $table->boolean( 'recruit' )->default(0);
	        $table->boolean( 'reservist' )->default(0);
	        $table->timestamps();
	        $table->foreign( 'server_id' )
	              ->references( 'id' )->on( 'servers' )
	              ->onDelete( 'cascade' )
	              ->onUpdate( 'cascade' );

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('server_wg_auth_notify_auth_error_groups');
    }
}
