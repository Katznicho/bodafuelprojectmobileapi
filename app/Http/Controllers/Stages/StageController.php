<?php

namespace App\Http\Controllers\Stages;

use App\Http\Controllers\Controller;
use App\Models\Boda\BodaUserModel;
use App\Models\Stages\StageModel;
use App\Models\UserTotalModel;
use App\Traits\HelperTrait;
use App\Traits\LogTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class StageController extends Controller
{
    use LogTrait, HelperTrait;
    //

    public function getDailyBodaStages(Request $request){
        $this->validate($request,[
            'user_id' => 'required'
        ]);
        //get the daily boda stages basing on the user id and created at is today
        $dailyBodaStages = StageModel::where('user_id',$request->user_id)->whereDate('created_at',today())->get();
        if($dailyBodaStages){
            //return the daily boda stages
            return response()->json([
                'message'=>'success',
                'data' => $dailyBodaStages
            ],Response::HTTP_OK);
        }else{
            return response()->json([
                'message' => 'No stages found'
            ],Response::HTTP_NOT_FOUND);
        }


    }
    public function registerStage(Request $request)
    {
        $loggedInUser = Auth::user();

        $this->validate($request, [
            'stageName' => 'required',
            'fuelStationId' => "required",
            'districtCode' => "required",
            'countyCode' => "required",
            'subCountyCode' => "required",
            'parishCode' => "required",
            'villageCode' => "required",
            "latitude" => "required",
            "longitude" => "required"

        ]);

        $success = StageModel::create([
            'stageName' => strtoupper($request->stageName),
            "stageStatus" => "0",
            'fuelStationId' => $request->fuelStationId,
            'districtCode' => $request->districtCode,
            'countyCode' => $request->countyCode,
            'subCountyCode' => $request->subCountyCode,
            'parishCode' => $request->parishCode,
            'villageCode' => $request->villageCode,
            "latitude" => $request->latitude,
            "longitude" => $request->longitude,
            'user_id' => $loggedInUser->adminId,
        ]);

        if ($success) {
            //get the current logged in user
            $user = auth()->user();
            //check the user total for the current user
            $userTotal = UserTotalModel::where("user_id",$user->adminId)->first();
                if($userTotal){
                    $userTotal->daily_boda_stages = $userTotal->daily_boda_stages + 1;
                    $userTotal->save();
                }else{
                    UserTotalModel::create([
                        "user_id" => $user->adminId,
                        "daily_boda_stages" => 1
                    ]);
                }
            $this->createActivityLog("Registered Stage", "Stage Registered successfully");
            return response(['message' => "success", "data" => $success, "statuCode" => Response::HTTP_OK], Response::HTTP_OK);
        } else {
            return response(['message' => "Failure", "data" => "Error Please Try again", "statusCode" => Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
        }
    }


    public function fetchstages()
    {
        $stages = StageModel::all();
        return response(["message" => "success", "data" => $stages, "statusCode" => Response::HTTP_OK], Response::HTTP_OK);
    }

    //get registered stages today by  a user
    public function getRegisteredStages(Request $request){
        $validator = $this->validate($request, [
            'user_id' => 'required'
        ]);
         $user_stages =  StageModel::where("user_id", $request->user_id)->whereDate('created_at', today())->get();

         if(count($user_stages)>0){
            return response(["message" => "success", "data" => $user_stages, "statusCode" => Response::HTTP_OK], Response::HTTP_OK);
         }
         else{
            return response(["message" => "failure", "data" => []], Response::HTTP_OK);
         }
    }

    //get suspended stages
    public function getSuspendedStages(Request $request){
        $validator = $this->validate($request, [
            'user_id' => 'required'
        ]);
         $user_stages =  StageModel::where(
            [

                 ["user_id", $request->user_id],
                 ["stageStatus", "=", 2]
            ]

            )

         ->get();

         if(count($user_stages)>0){
            return response(["message" => "success", "data" => $user_stages, "statusCode" => Response::HTTP_OK], Response::HTTP_OK);
         }
         else{
            return response(["message" => "failure", "data" => []], Response::HTTP_OK);
         }

    }

    //get specific stage
    public function getSpecificStageOnBoarded(Request $request){
        $validator = $this->validate($request, [
            'user_id' => 'required',
            'stage_id'=>"required"
        ]);
        $stage_details =  StageModel::where("user_id", $request->user_id)->whereDate('created_at', today())->get();
        $boda_users_today = BodaUserModel::where('stageId', $request->stage_id)->whereDate('created_at', today())->get();
        return response(["message" => "success", "stage" => $stage_details, 'bodas'=>$boda_users_today], Response::HTTP_OK);


    }

    //
    public function activateRegisteredStage(Request $request){
        $validator = $this->validate($request, [
            'user_id' => 'required',
            'stage_id'=>"required"
        ]);

        $oneTymPin =  $this->randomkey(5);
        $hashedPin = $this->hashPass($oneTymPin);
        //$allbodaUsers =  $dbAccess->select("bodauser", ["bodaUserName", "bodaUserPhoneNumber"], ["stageId" => $id]);

        $allbodausers =  BodaUserModel::where('stageId', $request->stage_id)->get();
        if(count($allbodausers) >0){
             foreach($allbodausers as $bodauser){
                $message = "Hello " . $bodauser->bodaUserName. "Your  have been activated on CreditPlus Dail *217*212# to get started Remember your one time pin is " . $oneTymPin;
                $res = $this->sms_faster($message , array($this->formatMobileInternational($bodauser->bodaUserPhoneNumber)), 1);
             }
             //update the stage

             $updated_stage = StageModel::where("stageId", $request->stage_id)->update(["stageStatus" => '1']);
             if($updated_stage){
                  //activate bodas of this stage
                   $updatedbodas = BodaUserModel::where('stageId', $request->stage_id)->update(['bodaUserStatus' => '1', "pin" => $hashedPin]);
                   if($updatedbodas){
                    return response(["message" => "success", "data" => 'activitation successfull'], Response::HTTP_OK);
                   }
                   else{
                    return response(["message" => "failure", "data" => 'Failed to activate boda users'], Response::HTTP_OK);
                   }
             }
             else{
                return response(["message" => "failure", "data" => 'Failed to activate stage'], Response::HTTP_OK);
             }
        }
        else{
            return response(["message" => "failure", "data" => 'No boda users found'], Response::HTTP_OK);
        }

    }
}
