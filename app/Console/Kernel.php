<?php

namespace App\Console;

use App\Http\Controllers\CampaignController;
use App\Http\Requests\StoreCampaignRequest;
use App\Models\Campaign;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // check and send scheduled campaigns every minute
        $schedule->call(function () {

            // get scheduled campaigns
            $campaigns = Campaign::scheduledCampaigns()->get();

            foreach ($campaigns as $campaign) {
                // store campaign request instance
                $storeCampaignRequest = new StoreCampaignRequest(json_decode(json_encode($campaign->meta), true));

                // modify data
                unset($storeCampaignRequest['scheduled_for']);
                $storeCampaignRequest['company'] = $campaign->company;
                $storeCampaignRequest['campaign'] = $campaign;

                // send campain
                (new CampaignController())->create($storeCampaignRequest);
            }
        })->everyMinute();
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
