<?php

namespace App\Console;
// use App\Console\Commands\AutoLeechFaloo;
// use App\Console\Commands\AutoLeechFanqie;
use Carbon\Carbon;
use App\TuLuyen\Model_charater;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\UpdateViewDayStory;
use App\Console\Commands\ResetNominationStory;
use App\Console\Commands\UpdateViewWeekStory;
use App\Console\Commands\ResetOverCount;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		UpdateViewDayStory::class,
		UpdateViewWeekStory::class,
        ResetNominationStory::class,
        ResetOverCount::class,
		// AutoLeechFaloo::class,
		// AutoLeechFanqie::class

	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('story:reset_view_day')->daily();
		$schedule->command('check-vip-expired')->daily()->at('23:59');
		$schedule->command('story:reset_view_week')->weekly();
        $schedule->command('story:reset_nomination')->weekly();
		$schedule->command('ranking-cron')->monthlyOn(28)->at('00:00');
        $schedule->command('overcount:reset_month')->monthly();
		$schedule->command('check-bank-transfer')->everyMinute()->withoutOverlapping();
		//$schedule->command('leech:faloo')->everyFiveMinutes()->withoutOverlapping();
		//$schedule->command('leech:fanqienovel')->everyFiveMinutes()->withoutOverlapping();
		//$schedule->command('leech:uukanshu')->everyFiveMinutes()->withoutOverlapping();
		//$schedule->command('leech:trxs')->everyFiveMinutes()->withoutOverlapping();
		//$schedule->command('leech:xinyushuwu')->everyFiveMinutes()->withoutOverlapping();
		//$schedule->command('autoleech:faloo')->everyFiveMinutes()->withoutOverlapping();
		//$schedule->command('autoleech:fanqie')->everyFiveMinutes()->withoutOverlapping();
		// $schedule->command('inspire')->hourly();

		$schedule->call(function () {
			//user is offline after 5 minutes
			$users = Model_charater::where('time_last_online', '<', Carbon::now()->subMinutes(5)->toDateTimeString())
				->update(['is_online' => false]);
		})->everyMinute();

		$schedule->call(function () {
			Model_charater::chunk(100, function ($users) {
				foreach ($users as $user) {
					if ($user->luk > 1) {
						$rand = rand(1, $user->luk);

						$user->update([
							'luk' => $rand
						]);
					}
				}
			});
		})->dailyAt('08:00');

	}

	/**
	 * Register the commands for the application.
	 *
	 * @return void
	 */
	protected function commands()
	{
		$this->load(__DIR__ . '/Commands');

		require base_path('routes/console.php');
	}
}
