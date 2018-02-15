<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use App\Http\Controllers\TeamSpeakUserAuth;
use App\Http\Controllers\TeamspeakWn8GroupController;
use App\Http\Controllers\TeamSpeakWotPlayersController;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\TeamspeakVerifyGameNicknameController;

class Kernel extends ConsoleKernel {
	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		//
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule $schedule
	 *
	 * @return void
	 */
	protected function schedule( Schedule $schedule ) {

		$schedule->call( function () {
			$TeamspeakWn8GroupController = new TeamspeakWn8GroupController();
			$TeamspeakWn8GroupController->UserChengeGroupCron();
		} )->everyMinute();

		$schedule->call( function () {
			$TeamSpeakWotPlayersController = new TeamSpeakWotPlayersController();
			$TeamSpeakWotPlayersController->UserChengeGroupCron();
		} )->everyMinute();

		$schedule->call( function () {
			$TeamspeakVerifyGameNicknameController = new TeamspeakVerifyGameNicknameController();
			$TeamspeakVerifyGameNicknameController->UserChengeGroupCron();
		} )->everyMinute();

		$schedule->call( function () {
			$TeamSpeakUserAuth = new TeamSpeakUserAuth();
			$TeamSpeakUserAuth->UserChengeGroupCron();
		} )->everyMinute();

	}

	/**
	 * Register the commands for the application.
	 *
	 * @return void
	 */
	protected function commands() {
		$this->load( __DIR__ . '/Commands' );

		require base_path( 'routes/console.php' );
	}
}
