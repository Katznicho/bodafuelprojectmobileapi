<?php

namespace App\Console\Commands;

use App\Models\Boda\BodaUserModel;
use App\Models\LoanModel;
use App\Models\Stages\StageModel;
use App\Traits\HelperTrait;
use Illuminate\Console\Command;

class DeactivateBodaUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:deactivatebodauser';

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

    public function getMobileNumbers($bodaUsers)
    {
        $mobileNumbers = [];

        foreach ($bodaUsers as $bodaUser) {
            $mobileNumbers[] = $this->formatMobileInternational($bodaUser->bodaUserPhoneNumber);
        }

        return $mobileNumbers;
    }


    public function formatMobileInternational($mobile)
    {
        $length = strlen($mobile);
        $m = '+256';
        //format 1: +256752665888
        if ($length == 13)
            return $mobile;
        elseif ($length == 12) //format 2: 256752665888
            return "+" . $mobile;
        elseif ($length == 10) //format 3: 0752665888
            return $m .= substr($mobile, 1);
        elseif ($length == 9) //format 4: 752665888
            return $m .= $mobile;

        return $mobile;
    }

    public function sms_faster($message, $receiver)
    {
        $receipients = $receiver;
        // $receipients = substr($receipients, 0, -1);
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.africastalking.com/version1/messaging');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS,  "username=thinkxcloud" . "&to=" . urlencode($receipients) . "&message=" . urlencode($message));

        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $headers[] = 'Apikey: bb48eaa0bbb148854918bac1fb0577e1289725dd392978dd266837f9f2ec2d5b';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return substr($result, 0, 4); // "1701" indicates success */

    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $bodaUsers = BodaUserModel::whereIn('bodaUserStatus', [2, 3])->get();

        if ($bodaUsers->count() > 0) {

            foreach ($bodaUsers as $bodaUser) {
                if ($bodaUser->bodaUserStatus == 3) {
                    // Update the loan penalty by 1000

                    LoanModel::where( [
                        ["boadUserId", '=', $bodaUser->bodaUserPhoneNumber],
                        ['status', '=', '1']
                    ])
                        ->increment('loan_penalty', 1000);
                } else {
                    // Update the status to 3 for the boda user
                    $bodaUser->bodaUserStatus = 3;
                    $bodaUser->save();

                    //update the loan penalty by 1000
                    LoanModel::where([
                        ["boadUserId", '=', $bodaUser->bodaUserPhoneNumber],
                        ['status', '=', '1']
                    ])
                        ->increment('loan_penalty', 1000);

                    // Check if the boda user has a stageId
                    if ($bodaUser->stageId) {
                        // Retrieve all boda users with the same stageId
                        $stageBodaUsers = BodaUserModel::where('stageId', $bodaUser->stageId)->get();

                        // Suspend all boda users of that stage by updating their status to 3
                        foreach ($stageBodaUsers as $stageBodaUser) {
                            //$stageBodaUser->bodaUserStatus = 3;
                            //$stageBodaUser->save();
                            // Add the message to the array
                            $message = "Hello " . $stageBodaUser->bodaUserName . ", your stage has been suspended  on CreditPlus  because  $bodaUser->bodaUserName has failed to pay . Please contact the administrator for more information";
                            //info("Sending message to " . $stageBodaUser->bodaUserName . " on " . $stageBodaUser->bodaUserPhoneNumber);
                            $res = $this->sms_faster($message, $this->formatMobileInternational($stageBodaUser->bodaUserPhoneNumber));
                            //info("Message sent to " . $stageBodaUser->bodaUserName . " on " . $stageBodaUser->bodaUserPhoneNumber);
                            //info("Message response: " . $res);


                        }

                        // Suspend the stage
                        $stage = StageModel::where('stageId', $bodaUser->stageId)->first();
                        $stage->stageStatus = 2;
                        $stage->save();
                        info("Stage has been suspended.");
                    }
                }
            }
        } else {
            info("No boda users to deactivate.");
        }
    }
}