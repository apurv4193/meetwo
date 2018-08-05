<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Helpers;
use Log;

class UpdateExistingUserScore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateUserScore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Existing User Score based on updated user detail';

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
     * @return mixed
     */
    public function handle()
    {
        $updateUserScore = Helpers::updateExistingUserScore();
       
        if (count($updateUserScore)) {
            for ($i = 0; $i < count($updateUserScore); $i++) {
                try {
                    $userScoreUpdate = Helpers::updateUserScore($updateUserScore[$i]['id']);
                    $scoreUpdate = Helpers::updateUserTotalScoreById($updateUserScore[$i]['id']);
                }  catch (Exception $e) {
                    Log::error($updateUserScore[$i]['id'] . " # score not Updated by cron job #");
                }
            }
        }
 
        $this->info('Existing users score updated successfully!');
    }
}
