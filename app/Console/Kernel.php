<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

function _ ($s = '') { echo $s . "\r\n"; }

class Kernel extends ConsoleKernel
{
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
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $s_dir_path = __DIR__ . '/../Schedule/';

        $dh = opendir($s_dir_path);

        while($f = readdir($dh)) {
            $extension = explode('.', $f);
            $extension = strtolower(array_pop($extension));

            if ($extension == 'php') {
                require_once ($s_dir_path . $f);    
            }
        }

        closedir($dh);
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
