<?php

namespace App\Console\Commands;

use App\Models\UserTotalModel;
use App\Models\UserTotalPerDayModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateUserTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updateusertotals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        //get all todays totals from usertotalsmodel basing on date today
        $todayTotals = UserTotalModel::whereDate('updated_at', today())->get();
        //if the today totals are not empty
        if (!$todayTotals->isEmpty()) {
            //loop through the today totals
            foreach ($todayTotals as $todayTotal) {
               //move them to the user total per day model
                UserTotalPerDayModel::create(
                    [
                        'user_id' => $todayTotal->user_id,
                        'today_boda_riders' => $todayTotal->daily_boda_riders,
                        'today_fuel_stations' => $todayTotal->daily_fuel_stations,
                        'today_boda_stages' => $todayTotal->daily_boda_stages,
                    ]

                    );

                    //add the current daily totals to the total totals and then set the daily totals to zero
                    $todayTotal->total_boda_riders += $todayTotal->daily_boda_riders;
                    $todayTotal->total_fuel_stations += $todayTotal->daily_fuel_stations;
                    $todayTotal->total_boda_stages += $todayTotal->daily_boda_stages;
                    $todayTotal->daily_boda_riders = 0;
                    $todayTotal->daily_fuel_stations = 0;
                    $todayTotal->daily_boda_stages = 0;
                    $todayTotal->save();

            }
        }
        // Log::info('UpdateUserTotals command run successfully');


        //create a file in the storage folder and write the date and time of the last time the command was run if the command was run successfully and if the command was not run successfully write the error message and if the file already exists append the new data to the file
        $file = fopen(storage_path('logs/updateusertotals.txt'), 'a');
        if ($file) {
            fwrite($file, 'The command was run successfully at ' . now() . '');
            fclose($file);
        } else {
            //create another file in the storage folder and write the error message if the file was not created
            //$file = fopen(storage_path('logs/updateusertotals.txt'), 'a');
            Log::error('The command was not run successfully at ' . now() . '');
        }




    }
}
// * * * * * cd /var/www/html/bodafuelprojectmobileappapi && php artisan schedule:run >> /dev/null 2>&1
